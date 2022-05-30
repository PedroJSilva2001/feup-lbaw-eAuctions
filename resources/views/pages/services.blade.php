@extends('layouts.app')

@section('title', 'Services')

@section('content')

<div class="container">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  mx-3">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Services</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-sm-3 col-4">
            <nav class ="navbar navbar-light navbar-expand-md">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarServicesContent" aria-controls="navbarServicesContent" aria-expanded="true" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span> Domains
                </button>
                <div class="collapse navbar-collapse flex-column align-items-start" id="navbarServicesContent" role="tablist" aria-orientation="vertical">
                    <a class="nav-link same-page" id="services-user" data-toggle="pill" href="#list-user" role="tab" aria-controls="services-user" aria-selected="true">User Interaction</a>
                    <a class="nav-link same-page" id="services-auctions" data-toggle="pill" href="list-auctions" role="tab" aria-controls="v-pills-profile" aria-selected="false">Auctions</a>
                    <a class="nav-link same-page" id="services-admins" data-toggle="pill" href="#list-admins" role="tab" aria-controls="v-pills-messages" aria-selected="false">Administration</a>
                </div>
            </nav>
        </div>
        
        <div class="col-8">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="list-user" role="tabpanel">
                    <h3>User Interaction</h3>
                    <p>
                        Upon registry as a user of our website, you pledge to keep every auction a safe place, 
                        <b>free of harsh language and offensive comments</b>. 
                    </p>
                    <p>Users who do not respect this rule <b>will be blocked</b>.</p>

                    <ul class="list-group">
                        <li class="list-group-item"><b>Bidders:</b>
                            <ul class="list-group">
                                <li>A user cannot bid if his bid is the current highest.</li>
                            </ul>
                        </li>
                        <li class="list-group-item"><b>Sellers:</b>
                            <ul class="list-group">
                                <li>Can edit its auctions before publising them or cancel it.</li>
                            </ul>
                        </li>
                    </ul>

                </div>
                <div class="tab-pane fade show" id="list-auctions" role="tabpanel" style="display: none;">
                    <h3>Auctions</h3>
                    <ul class="list-group">
                        <li class="list-group-item">An auction's date must always be set in the future, having its closing date must be at least 24 hours after its creation date.</li>
                        <li class="list-group-item">A new bid must be strictly greater than the previous bid.</li>
                        <li class="list-group-item">An auction can only be cancelled if no bids have been made.</li>
                    </ul>                   
                </div>
                <div class="tab-pane fade show" id="list-admins" role="tabpanel" style="display: none;">
                    <h3>Administration</h3>
                    <p>
                        <b>Admins</b> are responsible for the management of users and for some specific supervisory and moderation functions, 
                        removing comments or cancelling auctions.
                    </p>
                    <p>They also have the ability to <b>block</b> users.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection