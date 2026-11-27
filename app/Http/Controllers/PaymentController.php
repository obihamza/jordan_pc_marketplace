<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $payments = Payment::with('order.user')
                ->latest()
                ->paginate(15);

            return view('admin.payments.index', compact('payments'));
        } elseif ($me->isSeller()) {
            $payments = Payment::with('order')
                ->whereHas('order.items', function ($query) use ($me) {
                    $query->where('seller_id', $me->id);
                })
                ->latest()
                ->paginate(15, ['*'], 'sales_page');

            $myPayments = Payment::with('order')
                ->whereHas('order', function ($query) use ($me) {
                    $query->where('user_id', $me->id);
                })
                ->latest()
                ->paginate(15, ['*'], 'purchases_page');

            return view('seller.payments.index', compact('payments', 'myPayments'));
        } else {
            $payments = Payment::with('order')
                ->whereHas('order', function ($query) use ($me) {
                    $query->where('user_id', $me->id);
                })
                ->latest()
                ->paginate(15);

            return view('buyer.payments.index', compact('payments'));
        }
    }

    public function update(Request $request, Payment $payment)
    {
        $me = auth()->user();

        if (! $me->isAdmin()) {
            abort(403);
        }

        $payment = Payment::findOrFail($payment->id);

        $validated = $request->validate([
            'payment_status' => 'required|in:pending,authorized,paid,failed,refunded',
        ]);

        $payment->update($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully!');
    }
}
