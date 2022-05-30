@extends('layouts.app')

@section('content')

@if (!Auth::guest())
    @include('partials.main_profile', array(
        'title'    => "Drafts",
        'array'    => $draftAuctions
    ))
@endif
@endsection