@extends('layouts.app')

@section('head')
    <script src={{ asset('js/settings.js') }} defer></script>
@endsection

@section('content')

<div class="container mt-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  mx-3">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/users/' . $owner->id) }}">Profile</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-sm-2 col-3 border-width">
            <nav class ="navbar navbar-light navbar-expand-md" style="display: block;">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSettingsContent" aria-controls="navbarSettingsContent" aria-expanded="true" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span> Options
                </button>
                <div class="collapse navbar-collapse" id="navbarSettingsContent">
                    <ul class="nav navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" id="account"  href="{{ route('show_settings', $owner->id) }}">Account</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="payments" href="{{ route('show_payments', $owner->id) }}">Payments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="delete-account" href="{{ route('show_delete', $owner->id) }}">Delete Account</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="col-md profile-settings">
            <h3>{{ $title }}</h3>
            <div class="col-md-8">
                @if ($title == "My Account")
                    <form method="POST" action="{{ route('update', $owner->id) }}" enctype="multipart/form-data">
                        {{ csrf_field() }}

                        <div class="form-group m-3 forms-info">
                            <label for="name">Name</label>
                            <input id="name" type="text" class="form-control" name="name" placeholder="{{ $owner->name }}" autofocus>
                        </div>    

                        <div class="form-group m-3 forms-info">
                            <label for="username">Username</label>
                            <input id="username" type="text" class="form-control" name="username" placeholder="{{ $owner->username }}" autofocus>
                        </div>  

                        <div class="form-group m-3 forms-info">
                            <label for="email">Email</label>
                            <input id="email" type="email" class="form-control" name="email" placeholder="{{ $owner->email }}" autofocus>
                        </div>  

                        <div class="form-group m-3 mt-5 forms-info">
                            <label for="oldpassword">Current Password</label>
                            <input id="oldpassword" type="password" class="form-control" name="oldpassword" autofocus>
                        </div>  

                        <div class="form-group m-3 forms-info">
                            <label for="password">New Password</label>
                            <input id="password" type="password" class="form-control" name="password">
                            @if ($errors->has('password'))
                                <span class="error">
                                    {{ $errors->first('password') }}
                                </span>
                            @endif
                        </div> 

                        <div class="form-group m-3 forms-info">
                            <label for="password-confirm">Confirm New Password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation">
                        </div> 
                    
                        <div class="form-group m-3 forms-info">
                            <label for="profile_image" class="form-label">Picture</label>
                            <input class="form-control" type="file" id="profile_image" accept="profile_image/png, profile_image/gif, profile_image/jpeg" name="profile_image" style="font-size:1em">
                        </div>
                        

                        <div class="row">
                            <div class="col text-center">
                                <button type="submit" class="btn btn-lg enter-info">Save</button>
                            </div>
                        </div>

                        @if (session('success'))
                            <div class="col-sm-12">
                                <div class="alert  alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                </div>
                            </div>
                        @elseif (session('error'))
                            <div class="col-sm-12">
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                    </div>
                            </div>
                        @endif
                    </form>
                @endif
            </div>
            @if ($title == "Payments")
                <label class="mb-3"> Transactions </label>
                <div class="accordion accordion-flush " id="accordionParent">
                    <div class="accordion-item">
                        <h2 class="m-0 accordion-header w-100" id="addCreditHeader">
                            <a class="accordion-button btn accordion-btn-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#selectAdd"  aria-expanded="false" aria-controls="selectAdd"> 
                                Add Credit
                            </a>
                        </h2>
                        <div class="accordion-collapse collapse" aria-labelledby="#addCreditHeader" data-bs-parent="#accordionParent" id="selectAdd" name="add">
                            <div class="col-md d-flex justify-content-center">
                                <div class="accordion-body">
                                    <form method="POST" action="{{ route('transaction_request', $owner->id) }}" onkeydown="return event.key != 'Enter';" enctype="multipart/form-data" class="m-0 d-flex flex-column justify-content-center">
                                        {{ csrf_field() }}

                                        <div class="form-group" style="position: relative;">

                                            <h6 class="mb-3 mx-2 text-center"> Available Credit: {{ $owner->credit }} €</h6> 

                                            <span class="my-auto" style="position: absolute; right: 26em; bottom: 1.6em; z-index: 1;"> € </span>

                                            <input id="amount" type="number" class="form-control text-center "  style="position: relative" name="amount" inputmode="numeric" 
                                            min="1" value="1" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                        </div> 

                                        <div class="row d-flex justify-content-between m-2">
                                            <div class="col-6 text-center">
                                                <button type="button" class="btn btn-lg enter-info" id="transfer-btn" data-bs-toggle="modal" data-bs-target="#transfer-modal">Pay With Transfer</button>
                                            </div>
                                            <div class="col-6 text-center" >
                                                <button type="submit" class="btn btn-lg enter-info" name='action' value="PayPal">Pay With PayPal</button>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="transfer-modal" tabindex="-1" role="dialog" aria-labelledby="transfer-modal-title" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="transfer-modal-title"><b>Pay With Transfer</b></h4>
                                                        <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true" style="font-size: 2em;">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex flex-column justify-content-center" style="margin: 0em 4em;">
                                                            <h5 class="m-2"> IBAN: PT50003598175015500848769 </h5>
                                                            <h5 class="m-2" id="transfer-sum"> Sum: {{ Request::input('amount') }} </h5>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer d-flex justify-content-center">
                                                        <button type="button" class="btn btn-lg px-5 mx-1 enter-info" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-lg px-5 mx-1 enter-info" name='action' value="Transfer" data-bs-dismiss="modal">Confirm </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="m-0 accordion-header w-100" id="takeCreditHeader">
                            <a class="accordion-button btn accordion-btn-color collapsed" data-bs-toggle="collapse" data-bs-target="#selectTake"  aria-expanded="false" aria-controls="selectTake"> 
                                Widthdraw Credit
                            </a>
                        </h2>
                        <div class="accordion-collapse collapse" aria-labelledby="#takeCreditHeader" data-bs-parent="#accordionParent" id="selectTake" name="take">
                            <div class="col-md d-flex justify-content-center">
                                <div class="accordion-body">
                                    <form method="POST" action="{{ route('transaction_request', $owner->id) }}" onkeydown="return event.key != 'Enter';" enctype="multipart/form-data" class="m-0 d-flex flex-column justify-content-center">
                                        {{ csrf_field() }}

                                        <div class="form-group" style="position: relative;">

                                            <h6 class="mb-3 mx-2 text-center"> Available Credit: {{ $owner->credit }} €</h6> 

                                            <span class="my-auto" style="position: absolute; right: 16em; bottom: 1.6em; z-index: 1;"> € </span>

                                            <input id="amount" type="number" class="form-control text-center "  style="position: relative" name="amount" inputmode="numeric" 
                                            max="0" value="-1" pattern="[0-9-]+">
                                        </div>  
                                        <div class="row-md">
                                                <button type="button" class="btn btn-lg w-100 enter-info" id="transfer-btn" data-bs-toggle="modal" data-bs-target="#take-modal">
                                                    WidthDraw</button>
                                        </div>
                                        <div class="modal fade" id="take-modal" tabindex="-1" role="dialog" aria-labelledby="take-modal-title" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title" id="transfer-modal-title"><b>WidthDraw Credit</b></h4>
                                                        <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true" style="font-size: 2em;">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex flex-column justify-content-center" style="margin: 0em 4em;">
                                                            <label> Insert IBAN: </label>
                                                            <input  type="text" class="form-control text-center "  style="position: relative" 
                                                             min="1" value="PT50" pattern="^([A-Z]{2}[ \-]?[0-9]{2})(?=(?:[ \-]?[A-Z0-9]){9,30}$)((?:[ \-]?[A-Z0-9]{3,5}){2,7})([ \-]?[A-Z0-9]{1,3})?$">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer d-flex justify-content-center">
                                                        <button type="button" class="btn btn-lg px-5 mx-1 enter-info" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-lg px-5 mx-1 enter-info" name='action' value="Transfer" data-bs-dismiss="modal">Confirm</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if (session('success'))
                                            <div class="col-sm-12">
                                                <div class="alert  alert-success alert-dismissible fade show" role="alert">
                                                    {{ session('success') }}
                                                </div>
                                            </div>
                                        @elseif (session('error'))
                                            <div class="col-sm-12">
                                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                        {{ session('error') }}
                                                    </div>
                                            </div>
                                        @endif
                        
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <label class="my-3"> Payments </label>
                <div class="col-md">
                    @if ($payments->isEmpty())
                        <span class="text-muted"> No Payments. </span>
                    @else
                        @foreach ($payments as $transaction)
                            @include('partials.transactions_card', array(
                                'id'            => $transaction->id,
                                'user'          => $transaction->user_id,
                                'value'         => $transaction->value,
                                'description'   => $transaction->description,
                                'date'          => $transaction->date,
                                'method'        => $transaction->method,
                                'status'        => $transaction->status,
                                'isSettings'    => true
                            ))
                        @endforeach
                        <div class="d-flex">
                            <div class="mx-auto my-auto">
                                {{ $payments->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            @if ($title == "Delete Account")
                <p>Once you delete your account, there is no going back. Please be certain.</p>
                <form method="POST" action="{{ route('delete_account', $owner->id) }}">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-lg btn-outline-danger">Delete Account</button>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection