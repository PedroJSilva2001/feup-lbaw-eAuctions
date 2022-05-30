@extends('layouts.app')

@section('content')

@if (!Auth::guest())
    @include('partials.main_profile', array(
        'title'    => "Followed Auctions",
        'array'    => $followedAuctions
    ))
@endif
@endsection