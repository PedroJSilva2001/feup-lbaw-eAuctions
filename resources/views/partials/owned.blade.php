@extends('layouts.app')

@section('content') 
    
    @if (Auth::id() == $owner->id)
        @include('partials.main_profile', array(
            'title'    => "My Auctions",
            'array'    => $ownedAuctions
        ))
    @else
        @include('partials.main_profile', array(
            'title'    => "Auctions",
            'array'    => $ownedAuctions
        ))
    @endif

    @endsection