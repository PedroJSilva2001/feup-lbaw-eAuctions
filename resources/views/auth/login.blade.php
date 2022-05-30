@extends('layouts.app')

@section('content')

<div class="container-fluid fill-height px-0 ">
    <div class="card auth-card mx-auto">
        <div class="auth-title pt-4"><h2>Login</h2></div>

        <form method="POST" action="{{ route('login') }}">
            {{ csrf_field() }}

            <div class="form-group m-3 forms-info">
                <input id="email" type="email" class="form-control" name="email" placeholder="Email Address" value="{{ old('email') }}" required autofocus>
                @if ($errors->has('email'))
                    <span class="error">
                        {{ $errors->first('email') }}
                    </span>
                @endif
            </div>    

            <div class="form-group m-3 forms-info">
                <input id="password-login" type="password"  class="form-control" placeholder="Password" name="password" required>
                @if ($errors->has('password'))
                    <span class="error">
                        {{ $errors->first('password') }}
                    </span>
                @endif
            </div>

            <div class="float-right mx-4 my-3">
                <div class="form-check">
                    <span>
                        <input type="checkbox" class="form-check-input show-pass" onchange="showPassword();"> 
                        Show Passwords                        
                    </span>
                </div>
            </div>

            <div class="row text-end">
                <label>
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </label>
            </div>

            <div class="row">
                <div class="col text-center">
                    <button type="submit" class="btn btn-lg enter-info">Login</button>
                </div>
            </div>

            <hr class="m-4 mt-5">
            
            <div class="row text-center">
                <label>
                    Don't have an account? 
                    <a href="{{ route('register') }}" class="px-3">Sign Up</a>
                </label>
            </div>
        </form>
        
    </div>
</div>

@endsection
