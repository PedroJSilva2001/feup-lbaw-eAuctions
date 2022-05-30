@extends('layouts.app')

@section('content')

<div class="container-fluid fill-height px-0">
    <div class="card auth-card mx-auto">
        <div class="auth-title pt-4"><h2>Reset Password</h2></div>
        <form method="POST" action="{{ route('password.update') }}">
            {{ csrf_field() }}

            <div class="form-group m-3 forms-info">
                <input id="email" type="email" class="form-control" name="email" placeholder="Email Address" required>
                @if ($errors->has('email'))
                    <span class="error">
                        {{ $errors->first('email') }}
                    </span>
                @endif
            </div> 

            <div class="form-group m-3 forms-info">
                <input id="password" type="password" class="form-control" placeholder="Password" name="password" required>
                @if ($errors->has('password'))
                    <span class="error">
                        {{ $errors->first('password') }}
                    </span>
                @endif
            </div> 

            <div class="form-group m-3 forms-info">
                <input id="password-confirm" type="password" class="form-control" placeholder="Confirm Password" name="password_confirmation" required>
            </div> 

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="row text-center">
                <div class="col">
                    <button href="#" type="submit" class="btn btn-lg enter-info">Save</button>
                </div>
            </div>

        </form>
    </div>
</div>

@endsection
