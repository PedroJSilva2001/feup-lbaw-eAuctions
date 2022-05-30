@extends('layouts.generic')

@section('body')
  <body>
    @include('partials.navbar')
    @yield('content')
    @include('partials.footer')
  </body>
@endsection