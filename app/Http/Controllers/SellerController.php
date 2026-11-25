<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\User;
use App\Notifications\AdminEvent;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    /** Where national-ID pictures live (private disk: never reachable by a public URL). */
    private const ID_DIR = 'national-ids';

    public function index()
    {
        $me = auth()->user();

        if ($me && $me->isAdmin()) {

            // Admin sees every application, pending ones first.
            $sellers = Seller::with('user')
                ->withCount('products')
                ->orderByRaw("verification_status = 'pending' desc")
                ->paginate(15);

            return view('admin.sellers.index', compact('sellers'));
        }

        // Everyone else only sees approved sellers.
        $sellers = Seller::approved()
            ->with('user')
            ->withCount('products')
            ->paginate(15);

        if (! $me) {

            return view('guest.sellers.index', compact('sellers'));

        } elseif ($me->isSeller()) {

            return view('seller.sellers.index', compact('sellers'));
        }

        return view('buyer.sellers.index', compact('sellers'));
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            // Users who don't have a seller profile yet.
            $users = User::where('is_admin', false)->doesntHave('seller')->get();

            return view('admin.sellers.create', compact('users'));
        }

        if ($me->seller) {

            // Already applied (or already a seller): never a second application.
            abort_if($me->isSeller(), 403);

            return $me->seller->isRejected()
                ? redirect()->route('sellers.edit', $me->seller)
                    ->with('danger', 'Your application was rejected. Fix your details and send a new ID picture to apply again.')
                : redirect()->route('sellers.show', $me->seller);
        }

        // A buyer applies to become a seller.
        return view('buyer.sellers.create');
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        if (! $me->isAdmin() && $me->seller) {
            abort(403);
        }

        $rules = [
            'national_id' => 'required|string|max:30|unique:sellers,national_id',
            'official_name' => 'required|string|max:180|unique:sellers,official_name',
            // Applicants MUST send the ID picture; an admin creating a seller on someone's behalf may skip it.
            'national_id_image' => [$me->isAdmin() ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ];

        if ($me->isAdmin()) {
            $rules['user_id'] = 'required|exists:users,id|unique:sellers,user_id';
        }

        $validated = $request->validate($rules);

        $seller = Seller::create([
            'user_id' => $me->isAdmin() ? $validated['user_id'] : $me->id,
            'national_id' => $validated['national_id'],
            'official_name' => $validated['official_name'],
            'national_id_image' => $request->file('national_id_image')?->store(self::ID_DIR, 'local'),
            // An admin's own entry is trusted; an application waits for review.
            'verification_status' => $me->isAdmin() ? 'approved' : 'pending',
            'reviewed_at' => $me->isAdmin() ? now() : null,
        ]);

        if ($me->isAdmin()) {
            return redirect()->route('sellers.index')
                ->with('success', 'Seller profile created successfully!');
        }

        AdminEvent::send(
            'seller_application',
            'New seller application',
            $me->name.' applied to sell as "'.$seller->official_name.'".',
            route('sellers.show', $seller, false),
            ['seller_id' => $seller->user_id]
        );

        return redirect()->route('sellers.show', $seller)
            ->with('success', 'Application sent! An admin will review your ID and approve you soon.');
    }

    public function show(Seller $seller)
    {
        $me = auth()->user();

        $seller = Seller::with(['user', 'products' => fn ($q) => $q->available()])
            ->findOrFail($seller->user_id);

        // Not-yet-approved profiles are visible to the admin and to their owner only.
        if (! $seller->isApproved()) {
            abort_unless($me && ($me->isAdmin() || $me->id === $seller->user_id), 404);
        }

        if (! $me) {

            return view('guest.sellers.show', compact('seller'));

        } elseif ($me->isAdmin()) {

            return view('admin.sellers.show', compact('seller'));

        } elseif ($me->isSeller()) {

            return view('seller.sellers.show', compact('seller'));
        }

        return view('buyer.sellers.show', compact('seller'));
    }

    public function edit(Seller $seller)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $seller = Seller::with('user')->findOrFail($seller->user_id);

            return view('admin.sellers.edit', compact('seller'));
        }

        // Own profile only (an applicant can fix a pending/rejected application too).
        abort_unless($me->seller, 403);

        $seller = Seller::with('user')->findOrFail($me->id);

        return view('seller.sellers.edit', compact('seller'));
    }

    public function update(Request $request, Seller $seller)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $seller = Seller::findOrFail($seller->user_id);
        } else {
            abort_unless($me->seller, 403);
            $seller = Seller::findOrFail($me->id);
        }

        $rules = [
            'national_id' => ['required', 'string', 'max:30', Rule::unique('sellers', 'national_id')->ignore($seller->user_id, 'user_id')],
            'official_name' => ['required', 'string', 'max:180', Rule::unique('sellers', 'official_name')->ignore($seller->user_id, 'user_id')],
            'national_id_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ];

        // A rejected seller has to send a new picture to apply again.
        if (! $me->isAdmin() && $seller->isRejected()) {
            $rules['national_id_image'] = ['required', 'image', 'mimes:jpg,jpeg,png', 'max:4096'];
        }

        $validated = $request->validate($rules);

        $data = [
            'national_id' => $validated['national_id'],
            'official_name' => $validated['official_name'],
        ];

        $idChanged = $validated['national_id'] !== $seller->national_id;

        // Changing the ID number needs a matching new picture.
        if (! $me->isAdmin() && $idChanged && ! $request->hasFile('national_id_image')) {
            return back()->withInput()->withErrors(['national_id_image' => 'Upload a picture of the new national ID.']);
        }

        if ($request->hasFile('national_id_image')) {
            if ($seller->national_id_image) {
                Storage::disk('local')->delete($seller->national_id_image);
            }
            $data['national_id_image'] = $request->file('national_id_image')->store(self::ID_DIR, 'local');
        }

        // New ID details from the seller go back to the admin for review.
        $resubmitted = ! $me->isAdmin() && ($idChanged || $request->hasFile('national_id_image'));

        if ($resubmitted) {
            $data['verification_status'] = 'pending';
            $data['reviewed_at'] = null;
        }

        $seller->update($data);

        if ($resubmitted) {
            AdminEvent::send(
                'seller_application',
                'Seller application re-submitted',
                $me->name.' sent new ID details for "'.$seller->official_name.'".',
                route('sellers.show', $seller, false),
                ['seller_id' => $seller->user_id]
            );
        }

        return redirect()->route('sellers.show', $seller)
            ->with('success', $resubmitted
                ? 'Sent for review. An admin will check your new ID details.'
                : 'Seller profile updated successfully!');
    }

    public function destroy(Seller $seller)
    {
        $me = auth()->user();

        if (! $me->isAdmin()) {
            abort(403);
        }

        $seller = Seller::findOrFail($seller->user_id);

        $seller->delete();

        return redirect()->route('sellers.index')
            ->with('danger', 'Seller profile deleted successfully!');
    }

    /** Admin: accept the application. */
    public function approve(Seller $seller)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $seller->update(['verification_status' => 'approved', 'reviewed_at' => now()]);
        $this->clearApplicationNotices($seller);

        if ($seller->user) {
            AdminEvent::sendTo(
                $seller->user,
                'seller_approved',
                'You are now a seller!',
                'Your application was approved. You can list your products now.',
                route('seller.dashboard', [], false)
            );
        }

        return back()->with('success', $seller->official_name.' is now an approved seller.');
    }

    /** Admin: refuse the application (the user can fix it and apply again). */
    public function reject(Seller $seller)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $seller->update(['verification_status' => 'rejected', 'reviewed_at' => now()]);
        $this->clearApplicationNotices($seller);

        return back()->with('danger', $seller->official_name.' was rejected.');
    }

    /** Once reviewed, the bell stops showing this application as new (for every admin). */
    private function clearApplicationNotices(Seller $seller): void
    {
        DatabaseNotification::whereNull('read_at')
            ->where('type', AdminEvent::class)
            ->get()
            ->filter(fn ($n) => ($n->data['seller_id'] ?? null) == $seller->user_id)
            ->each->markAsRead();
    }

    /** The ID picture: only the admin and the owner may open it. */
    public function nationalId(Seller $seller)
    {
        $me = auth()->user();

        abort_unless($me->isAdmin() || $me->id === $seller->user_id, 403);
        abort_unless($seller->national_id_image && Storage::disk('local')->exists($seller->national_id_image), 404);

        return Storage::disk('local')->response($seller->national_id_image, null, ['Cache-Control' => 'private, no-store']);
    }
}
