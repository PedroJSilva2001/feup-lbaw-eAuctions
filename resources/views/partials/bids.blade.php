@extends('layouts.app')

@section('content')

@if (!Auth::guest())
    @include('partials.main_profile', array(
        'title'    => "Bidding History",
        'array'    => $biddingAuctions
    ))
@endif

@endsection