@extends('layouts.app')

@section('title', "Search")

@section('head')
@endsection

@section('content')

<div class="container-fluid mt-4">
    <div class="row h-100">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mx-3">
                <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page"> {{ $category }} </li>
            </ol>
        </nav>

        <h1 class="title-category"> {{ $category }} </h1>

        <!-- Category Auctions -->
        <main class="col pt-4 px-md-4 auctions-body" style="flex: 1">
            <div class="row row row-cols-1 row-cols-sm-2 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 d-flex justify-content-left">
                @foreach ($auctions_to_display as $auction)
                    @include('partials.auction_card', array(
                        'id'            => $auction->id,
                        'title'         => $auction->title,
                        'base_value'    => $auction->base_value,
                        'max_bid'       => $auction->getHighestBidValue(),
                        'time_left'     => $auction->getTimeDifference(),
                        'product_imgs'   => $auction->getAuctionPictures()
                    ))
                @endforeach
            </div>
        </main>

    </div>
</div>

@endsection