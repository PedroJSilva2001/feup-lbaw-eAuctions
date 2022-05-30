@extends('layouts.app')

@section('content')

@if (!Auth::guest())
    @include('partials.main_profile', array(
        'title'    => "Notifications",
        'array'    => $notifications
    ))
@endif
@endsection