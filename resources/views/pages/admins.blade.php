@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb ">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Administration</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-2 border-width">
            <nav class ="navbar navbar-light navbar-expand-md">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdminContent" aria-controls="navbarAdminContent" aria-expanded="true" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span> Options
                </button>
                <div class="collapse navbar-collapse" id="navbarAdminContent">
                    <ul class="nav navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" id="blocked" href="{{ url('/admins/blocked') }}">Blocked Users</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="transactions" href="{{ url('/admins/transactions-pending') }}">Transactions</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="col-md">
            <h3> {{ $title }} </h3>
                @if ($title == "Transactions")
                    <div class="row">
                        <nav class="navbar navbar-expand-lg navbar-light">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTransactions" aria-controls="navbarTransactions" aria-expanded="true" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span> Status
                        </button>
                            <div class="collapse navbar-collapse" id="navbarTransactions">
                                <ul class="navbar-nav d-lg-flex w-100 align-items-lg-end mx-2">
                                    <li class="nav-item">
                                        <a class="nav-link" id="pending" href="{{ url('/admins/transactions-pending') }}">Pending</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="accepted" href="{{ url('/admins/transactions-accepted') }}">Accepted</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="declined" href="{{ url('/admins/transactions-declined') }}">Declined</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                    <div class="col-md">
                        @if ($array->isEmpty())
                            <span class="text-muted"> No Transactions. </span>
                        @else
                            @foreach ($array as $transaction)
                                @include('partials.transactions_card', array(
                                    'id'            => $transaction->id,
                                    'user'          => $transaction->user_id,
                                    'value'         => $transaction->value,
                                    'description'   => $transaction->description,
                                    'date'          => $transaction->date,
                                    'method'        => $transaction->method,
                                    'status'        => $transaction->status,
                                    'isSettings'    => false
                                ))
                                @endforeach
                        @endif
                        <div class="d-flex">
                            <div class="mx-auto">
                                {{ $array->links() }}
                            </div>
                        </div>
                    </div>           
                @else
                    @if ($title == "Blocked Users")
                        @if ($blocks->isEmpty())
                            <span class="text-muted"> No Blocked Users. </span>
                        @else
                            <div class ="row px-3">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th class="p-4 align-middle" scope="col">User</th>
                                                <th class="p-4 align-middle" scope="col">Motive</th>
                                                <th class="p-4 align-middle" scope="col">Duration</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($blocks as $block)
                                            <tr>
                                                <td class="p-4 align-middle"><a href="{{ url('/users/' . $block->user_id) }}"> 
                                                    <?php 
                                                        echo App\Models\User::find($block->user_id)->username;  
                                                    ?>
                                                </a></td>
                                                <td class="p-4 align-middle">{{ $block->description }}</td>
                                                <td class="p-4 align-middle">@if ($block->end_date) {{ \Carbon\Carbon::parse($block->end_date)->diffForHumans() }} @else Indefinitely @endif </td>
                                                <td class="p-4 align-middle">
                                                    <form class="mb-0" method="POST" action="{{ route('unblock_user', $block->user_id) }}">
                                                        {{ csrf_field() }}
                                                        <input class="btn btn-sm enter-info m-0" type="submit" value="Unblock User">
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex align-center justify-content-center">
                            <div class="mt-5 pt-5 mx-auto">
                                {{ $blocks->links() }}
                            </div>
                        </div>
                    @endif
                @endif
        </div>
    </div>
</div>

@endsection