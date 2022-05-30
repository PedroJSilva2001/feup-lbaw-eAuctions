@extends('layouts.app')

@section('content')
<div class="container-fluid fill-height px-0">
    <div class="card auth-card mx-auto">
        <div class="auth-title pt-4"><h2>Send Password Reset Link</h2></div>

        <form action="{{ route('password.email') }}" method="POST">
            {{ csrf_field() }}

            <div class="form-group m-3 forms-info">
                <input id="email" type="email" class="form-control" name="email" placeholder="Email Address" required autofocus>
                @if ($errors->has('email'))
                    <span class="error">
                        {{ $errors->first('email') }}
                    </span>
                @endif
            </div>    
                      
            <div class="row text-center">
                <div class="col">
                    <button type="submit" class="btn btn-lg enter-info">
                        Send
                    </button>
                </div>
            </div>
        </form>
                        
    </div>             
</div>

@endsection
