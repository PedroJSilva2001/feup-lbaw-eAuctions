@extends('layouts.app')

@section('content')

<div class="container-fluid fill-height">
    <div class="card auth-card mx-auto pb-5">
        <div class="auth-title pt-4"><h2><b>BANNED</b></h2></div>

        <div class="row">
            <div class="text-center pb-5">
                Your Account is suspended.
            </div>

            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="color: var(--dark-blue);" class="bi bi-file-lock-fill" viewBox="0 0 16 16">
                <path d="M7 6a1 1 0 0 1 2 0v1H7V6zM6 8.3c0-.042.02-.107.105-.175A.637.637 0 0 1 6.5 8h3a.64.64 0 0 1 .395.125c.085.068.105.133.105.175v2.4c0 .042-.02.107-.105.175A.637.637 0 0 1 9.5 11h-3a.637.637 0 0 1-.395-.125C6.02 10.807 6 10.742 6 10.7V8.3z"/>
                <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm-2 6v1.076c.54.166 1 .597 1 1.224v2.4c0 .816-.781 1.3-1.5 1.3h-3c-.719 0-1.5-.484-1.5-1.3V8.3c0-.627.46-1.058 1-1.224V6a2 2 0 1 1 4 0z"/>
            </svg>
        </div>
    </div>
</div>

@endsection