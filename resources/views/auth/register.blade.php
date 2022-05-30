@extends('layouts.app')

@section('content')

<div class="container-fluid fill-height px-0 ">
    <div class="card auth-card mx-auto">
        <div class="auth-title pt-4"><h2>Register</h2></div>
        <form method="POST" action="{{ route('register') }}">
            {{ csrf_field() }}

            <div class="form-group m-3 forms-info">
                <input id="username" type="text" class="form-control" name="username" placeholder="Username" value="{{ old('username') }}" required autofocus>
                @if ($errors->has('username'))
                    <span class="error">
                        {{ $errors->first('username') }}
                    </span>
                @endif
            </div> 

            <div class="form-group m-3 forms-info">
                <input id="name" type="text" class="form-control" name="name" placeholder="Name" value="{{ old('name') }}" required autofocus>
                @if ($errors->has('name'))
                    <span class="error">
                        {{ $errors->first('name') }}
                    </span>
                @endif
            </div> 

            <div class="form-group m-3 forms-info">
                <input id="email" type="email" class="form-control" name="email" placeholder="Email Address" value="{{ old('email') }}" required>
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

            <div class="float-right mx-4 my-3">
                <div class="form-check">
                    <span>
                        <input type="checkbox" class="form-check-input show-pass" onchange="showPasswordRegister();"> 
                        Show Password                        
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col mt-5 text-center">
                    <button href="#" type="submit" class="btn btn-lg enter-info">Register</button>
                </div>
            </div>
            
            <hr class="m-4 mt-5">
            
            <div class="row text-center">
                <label>
                    Already have an account?  
                    <a href="{{ route('login') }}" class="px-3"> Login</a>
                </label>
            </div>
        </form>
        
    </div>
</div>

@endsection
