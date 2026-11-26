{{-- Logout button at the bottom of the dashboard side bar --}}
<div class="jo-sidebar-foot">
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="jo-logout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Logout</button>
    </form>
</div>
