{{-- Success messages float at the top of the screen (so they also show over the landing hero) --}}
@if(session('success'))
    <div class="jo-flash alert alert-success alert-dismissible fade show shadow" role="alert" id="joFlash">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        // Fade the message out by itself after a few seconds.
        setTimeout(function () {
            var el = document.getElementById('joFlash');
            if (el) { el.classList.remove('show'); setTimeout(function () { el.remove(); }, 300); }
        }, 5000);
    </script>
@endif

{{-- Validation errors stay inline above the page content --}}
@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
