@extends('layouts.app')

@section('content')

<div class="container">

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  mx-3">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">About Us</li>
        </ol>
    </nav>
    
    <div class="row text-center mb-5">
        <h2 class="h1-responsive text-center logo">eAuctions</h2>
    </div>
    
    <div class="col">

        <div class="row mb-5">
            <div class="col">
                <img width="400" height="250" src="https://allauctionsales.com/auctionsblog/wp-content/uploads/2019/06/Online-Auctions-715x400.jpg" alt="Auction story">
            </div>
            <div class="col">  
                <h3 style="font-weight: bold">Our Story</h3>
                <p>We came accross with an idea of creating a platform for managing auctions. We wanted to offer an accessible auction experience, allowing a global access to every auction in the platform. This aspect of our product was a clear motivation due to the universal impact of the COVID-19 pandemic in real-life events and communications, so ensuring that auctions can be created and proceed safely from the comfort of the users' homes was of the utmost importance. </p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col">  
                <h3 style="font-weight: bold">Our Mission</h3>
                <p>By creating this system, we intend to give both sellers and buyers an accessible auction experience for them to profit from their deals, through a global auction market. Sellers can then sell their products without local market restrictions, broadening their target audience, and buyers have access to a larger variety of products with more competitive prices.</p>
            </div>

            <div class="col">
                <span class="float-right">
                    <img width="300" height="250" src="https://cdn-icons-png.flaticon.com/512/3399/3399124.png" alt="Mission">
                </span>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <img width="400" height="250" src="https://www.clipartmax.com/png/full/145-1450301_why-we-are-different-business-team-png.png" alt="Community">
            </div>
            <div class="col">  
                <h3 style="font-weight: bold" >Our Community</h3>
                <p>Our team is a group of 4 soon-to-be informatics engineers.</p>
            </div>
        </div>
    </div>
</div>
@endsection