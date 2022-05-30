@extends('layouts.app')

@section('content')

<div class="container-fluid mt-5">

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mx-3">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-3 text-center border-right-2">
            <div class="row-md-2 row-2 pb-3">
                <img class="profile-image img-fluid img-thumbnail rounded-circle" src="{{ asset('assets/' . ( $owner->picture ? $owner->picture : 'profile_pictures/default.png')) }}" alt="">
            </div>
            <div class="row">
                
                <h3 class="m-0"><b>{{ $owner->name }} </b>  
                    @if (Auth::id() == $owner->id or $authUserisAdmin)       
                        <a id="personal-drafts" class="text-primary" href="{{ url('/users/' . $owner->id) . '/settings/account' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                            </svg>
                        </a>
                    @endif
                </h3>
                <p>&#x40;{{ $owner->username }}</p>


                <p class="m-0"><b>Rating:</b> {{ round($owner->rating, 2) }}</p>
                @if (Auth::id() == $owner->id or $authUserisAdmin)
                    <p class="m-0"><b>Credit:</b> {{ $owner->credit }} €</p>
                    <p><b>Followed Auctions:</b> {{ $followedAuctions->total() }}</p>
                @endif
                
                @if ($authUserisAdmin && $owner->id != Auth::id())
                    <ul class="nav navbar-nav">
                        @if (!$owner->isBlocked())
                            <form method="GET" action="{{ route('show_block_page', $owner->id) }}">
                                <input class="btn btn-lg enter-info" type="submit" value="Block User">
                            </form>
                        @else
                            <form  method="POST" action="{{ route('unblock_user', $owner->id) }}">
                                {{ csrf_field() }}
                                <input class="btn btn-lg enter-info" type="submit" value="Unblock User">
                            </form>
                        @endif
                    </ul>
                @endif
                
                @if (Auth::id() == $owner->id or $authUserisAdmin)

                    <nav class ="navbar navbar-light" style="display: block;">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarProfileContent" aria-controls="navbarProfileContent" aria-expanded="true" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span> Info
                        </button>
                        <div class="collapse navbar-collapse" id="navbarProfileContent">
                            <ul class="nav navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" id="bidding-history" href="{{ url('/users/' . $owner->id) . '/bids' }}">Bidding History</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="notifications" href="{{ url('/users/' . $owner->id) . '/notifications' }}">Notifications
                                        @if($nrUnreadNotifications > 0)
                                            <span class="ms-2 badge rounded-pill bg-danger"> {{ $nrUnreadNotifications }} </span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="personal-auction" href="{{ url('/users/' . $owner->id) . '/auctions' }}">My Auctions</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="draft-auction" href="{{ url('/users/' . $owner->id) . '/drafts' }}">Drafts</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="follow-auction" href="{{ url('/users/' . $owner->id) . '/followed' }}">Followed Auctions</a>
                                </li>
                            </ul>
                        </div>
                    </nav>

                    @if ($authUserisAdmin and Auth::id() == $owner->id)
                        <ul class="nav navbar-nav">
                            <li class="nav-link"><a class="btn btn-lg follow-auction-btn" href="{{ url('/admins/') }}" style="width:120px;">Administration</a></li>
                        </ul>
                    @endif

                @else
                    <nav class ="navbar" style="display: block;">
                        <ul class="nav navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link same-page" id="personal-auction" href="{{ url('/users/' . Auth::id()) . '/auctions' }}">Auctions</a>
                            </li>
                        </ul>
                    </nav>
                @endif
            </div>
        </div>

        <!-- <div class="col-md-1 px-0">
            <div class="d-flex" style="height: 100%;">
                <div class="vr"></div>
            </div>
        </div> -->

        <div class="col-md px-0 profile-main">

            <h3>{{ $title }}</h3>
            <div class="row row row-cols-1 row-cols-sm-2 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 d-flex">
                
                @if ($title == "Bidding History")
                    @if ($array->isEmpty())
                        <span class="text-muted px-3">You haven't bid in any auctions yet.</span>
                    @else
                        @foreach ($array as $result)
                            @include('partials.auction_card', array(
                                'id'            => $result->auction_id,
                                'title'         => App\Models\Auction::findOrFail($result->auction_id)->title,
                                'base_value'    => App\Models\Auction::findOrFail($result->auction_id)->base_value,
                                'max_bid'       => $result->value,
                                'time_left'     => \Carbon\Carbon::parse(\Carbon\Carbon::parse($result->date)->format('Y-m-d H:i:s'))->diffForHumans(), 
                                'product_imgs'  => App\Models\Auction::findOrFail($result->auction_id)->getAuctionPictures()
                            ))
                        @endforeach
                    @endif
                @else
                    @if ($title == "Notifications")
                        @if ($array->isEmpty())
                            <span class="text-muted px-3"> No notifications. </span>
                        @else
                            @foreach ($array as $notification)
                                @include('partials.notification_card', array(
                                        'type'            => $notification->type,
                                        'auction'         => $notification->getAssociatedAuction(),
                                ))
                            @endforeach
                        @endif
                    @else      
                        @if ($title == "My Auctions")
                            <div class="col mb-3 card-container d-flex">
                                <div class="card auction-card">
                                    
                                    <div class="card-body row d-flex align-items-center justify-content-center text-center">
                                        <a href="{{ url('/users/' . Auth::id()) . '/createAuction' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                            </svg> 
                                            
                                            <h4 class="card-title text-center text-truncate">Create New Auction</h4>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        @endif

                        @if ($array->isEmpty() and $title != "My Auctions")
                            <span class="text-muted px-3">No results.</span>
                        @else
                            @foreach ($array as $auction)
                                @include('partials.auction_card', array(
                                    'id'            => $auction->id,
                                    'title'         => $auction->title,
                                    'base_value'    => $auction->base_value,
                                    'max_bid'       => $auction->getHighestBidValue(),
                                    'time_left'     => $auction->getTimeDifference(),
                                    'product_imgs'  => $auction->getAuctionPictures()
                                ))
                            @endforeach
                        @endif
                    @endif
                @endif
                </div>
                <div class="d-flex align-center justify-content-center">
                    <div class="mt-5 pt-5 mx-auto">
                        {{ $array->links() }}
                    </div>
                </div>
            </div>  
        </div>
    </div> 

@endsection