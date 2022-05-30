@extends('layouts.app')

@section('content')

<div class="container-fluid">
    <div class="homepage-trending-auctions mx-auto">
        <main class="col ms-sm-auto pt-4 px-md-4 d-flex flex-column justify-content-center">
            <div class="pt-5 d-flex justify-content-between flex-wrap"> 
                <h2 class="col-6 trending-title">Trending Auctions </h2>
                <a class="col text-md-end pt-5 py-3 fs-2 view-all my-auto" href="{{ url('/search/') }}"> View All </a>
            </div>
            <div class="row row row-cols-1 row-cols-sm-2 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 d-flex justify-content-center">
                @foreach ($trending_auctions as $auction)
                    @if ($auction->type == "Public")
                        @include('partials.auction_card', array(
                            'id'            => $auction->id,
                            'title'         => $auction->title,
                            'base_value'    => $auction->base_value,
                            'max_bid'       => $auction->getHighestBidValue(),
                            'time_left'     => $auction->getTimeDifference(),
                            'product_imgs'  => $auction->getAuctionPictures()
                        ))
                    @else
                    @endif
                @endforeach
            </div>
        </main>
    </div>

    <div class="homepage-categories mx-auto">
        <div class="col ms-sm-auto pt-4 px-md-4 d-flex flex-column justify-content-center">
            <div class="text-md-start pt-5 m-0 d-flex justify-content-between flex-wrap"> 
                <h2 class="col-7 category-title">Trending Categories</h2>
            </div>
            <div class="row row row-cols-1 row-cols-sm-2 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 d-flex justify-content-center">
                @foreach ($trending_categories as $category)
                    <div class="col mb-3 text-center">
                        <div class="card category-card" style="background-color: {{ $category[2] }};">
                            <div class="row no-gutters">
                                <div class="col-md-7 text-center my-auto">
                                    <div class="card-body">
                                        <p class="card-text category-text"> {{ $category[0] }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4 category-img-container">
                                    <img src="{{ asset('assets/' . $category[1]) }}" class="category-img rounded-circle" alt="Category">
                                </div>
                            </div>
                            <a href="{{ url('/category/' . strtolower(str_replace(' & ', '-', $category[0]))) }}" class="stretched-link"></a> 
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>  

    <div class="homepage-past-auctions mx-auto">
        <div class="col ms-sm-auto pt-4 px-md-4 d-flex flex-column justify-content-center ">
            <div class="text-md-start pt-5 m-0 d-flex justify-content-between flex-wrap"> 
                <h2 class="col-7 past-title">Others loved this</h2>
            </div>
            <div class="row row row-cols-1 row-cols-sm-2 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 d-flex justify-content-center">
                @foreach ($past_auctions as $auction)
                    @if ($auction->type == "Public" || (!Auth::guest() && (Auth::user()->guestAuction($auction->id) != null)))   
                        @include('partials.auction_card', array(
                            'id'            => $auction->id,
                            'title'         => $auction->title,
                            'max_bid'       => $auction->getHighestBidValue(),
                            'base_value'    => $auction->base_value,
                            'brand'         => $auction->brand,
                            'time_left'     => $auction->getTimeDifference(),
                            'product_imgs'  => $auction->getAuctionPictures()
                        ))
                    @else
                    @endif
                @endforeach

            </div>
        </div>
    </div>  
</div>

@endsection