@extends('layouts.app')

@section('head')
    <script src="{{ asset('js/timer.js') }}"></script>
    <script src="{{ asset('js/bid.js') }}"></script>
    <script src="{{ asset('js/auction.js') }}" defer></script>
    
    <script>
        timer = new Timer();
        timer.start('{{ $auction->getTimeValue() }}', 'time-auction');
    </script>
@endsection

@section('content')

<div class="container-fluid mt-4">

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  mx-3">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item">
            @foreach ($auction->getCategories() as $category)
                <a href="{{ url('/category/' . strtolower(str_replace(' & ', '-', $category->category))) }}"> {{ $category->category }} </a>
                @if(!$loop->last)
                    ,
                @endif
            @endforeach
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $auction->title }}</li>
        </ol>
    </nav>

    <div class="container-fluid auction-page">
        <div class="container-fluid row d-flex justify-content-around">
            <h1 class="text-mb-start pt-5 pb-5">{{ $auction->title }} 
                @if (!Auth::guest() and (Auth::user()->id == $auction->getSeller()->id or Auth::user()->isadmin) and !$auction->isCancelled())
                    <a class="text-primary"id="personal-drafts" href="{{ url('/auctions/' . $auction->id . '/edit') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                            <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                            <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                        </svg>
                    </a>
                @endif
            </h1>
            <div class="row-md d-flex justify-content-center flex-wrap mb-5">  
                <div class="col-md-8 m-0 d-flex flex-column justify-content-center carousel-container">       
                    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            @foreach ($auction->getAuctionPicturesIndex() as $pics)
                                @if ($pics[1] == 0)
                                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="{{ 'Slide' . $pics[1] }}"></button>
                                @else
                                    <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $pics[1] }}" aria-current="true" aria-label="{{ 'Slide' . $pics[1] }}"></button>
                                @endif
                            @endforeach
                        </div>
                        <div class="carousel-inner">
                            @foreach($auction->getAuctionPicturesIndex() as $pics)
                                @if($pics[1] == 0)
                                    <div class="carousel-item active">
                                @else
                                    <div class="carousel-item">   
                                @endif
                                        <img src="{{ asset('assets/' . $pics[0]) }}" class="d-block w-100 img-fluid" alt="Product">
                                    </div>
                            @endforeach
                        </div>
                        
                        <div class="carousel-control-prev" data-bs-target="#carouselExampleIndicators"  data-bs-slide="prev">
                            <span class="carousel-control-prev-icon carousel-controls" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </div>
                        <div class="carousel-control-next" data-bs-target="#carouselExampleIndicators"  data-bs-slide="next">
                            <span class="carousel-control-next-icon carousel-controls" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </div>
                    </div>
                </div>
                <div class="col-md d-flex flex-column"> 
                    @if (!Auth::guest() and (Auth::user()->id != $auction->getSeller()->id))
                        <div class="follow-auction text-center">
                            @if (Auth::user()->isFollowingAuction($auction->id))
                                <a href="{{ route('unfollowAuction', $auction->id) }}" type="submit" role="button" class="btn btn-lg follow-auction-btn col-md-4">
                                    UNFOLLOW
                                </a>
                            @else
                                <a href="{{ route('followAuction', $auction->id) }}" type="submit" role="button" class="btn btn-lg follow-auction-btn col-md-4">
                                    FOLLOW
                                </a>
                            @endif
                        </div>
                    @endif
                
                    <div class="row auction-seller title-text text-center pt-5">
                        <a href="{{ url('/users/' . $auction->getSeller()->id) }}" class="d-flex">
                            <img src="{{ asset('assets/' . ( $auction->getSeller()->picture ? $auction->getSeller()->picture : 'profile_pictures/default.png')) }}"
                                class="rounded-circle seller-picture col-md-8 mb-5" alt="Seller" style="width: 150px; height: 150px;"> 
                            <div class="my-auto d-flex flex-column justify-content-center">
                                <h4 style= "font-weight: bold" class="m-0">{{ $auction->getSeller()->name }}</h4>
                                <h5 class="m-0" >&#x40;{{ $auction->getSeller()->username }}</h5>
                                <h5> Rating: {{ round($auction->getSeller()->rating, 2) }} </h5>
                            </div>
                        </a>

                        @if (!Auth::guest() and Auth::user()->hasBidAuction($auction->id))
                            <form method="POST" action="{{ route('rateSeller', $auction->id) }}">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <label for="score">Rate Seller</label>
                                    <input type="range" class="form-control-range" name="score" id="score" min="0" max="5" step="1" onInput="$('#rangeval').html($(this).val())">
                                    <span class="m-2" id="rangeval">3</span>
                                    <button type="submit" class="btn enter-info" name="action">Rate</button>
                                </div>
                                @if (session('success'))
                                    <div class="col-sm-12">
                                        <div class="alert  alert-success alert-dismissible fade show" role="alert">
                                            {{ session('success') }}
                                        </div>
                                    </div>
                                @elseif (session('error'))
                                    <div class="col-sm-12">
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ session('error') }}
                                        </div>
                                    </div>
                                @endif
                            </form>
                        @endif
                    </div>
                    <div class="row-md">
                        <div class="col-md-12 d-flex flex-column justify-content-center p-0" style="background-color:rgba(108, 138, 164, 0.3); border-radius: 40px">
                            @if (!Auth::guest() and (Auth::user()->id != $auction->getSeller()->id) and (\Carbon\Carbon::now() < $auction->end_date) and (\Carbon\Carbon::now() >= $auction->start_date))
                                <div class="m-auto text-center p-2"> 
                                    <h4 class="pt-4"style="font-weight: bold" > Bid </h4>
                                    
                                    <form class="col-md-12 m-auto d-flex justify-content-center" 
                                          onsubmit="Bid.submit(this, {{ Auth::user()->id }}, {{ $auction->id }}).then(() => Bid.updateCurrentBid({{ $auction->id }})); return false;">
    
                                        <div class="bid-input mb-2 p-0 d-flex justify-content-center">
    
                                            <div class="my-auto currency-symbol"> 
                                                <span> € </span>
                                            </div>
    
                                            @if ($auction->getHighestBidValue())
                                                <input type="number" class="bid-input form-control text-center mx-1" id="bid-on" inputmode="numeric"
                                                    min="{{ $auction->getHighestBidValue() + 1 }}" max="100000000" value="{{ $auction->getHighestBidValue() + 1 }}" name="value"
                                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                            @else 
                                                <input type="number" class="bid-input form-control text-center mx-1" id="bid-on" inputmode="numeric"
                                                    min="{{ $auction->base_value }}" max="100000000" value="{{ $auction->base_value }}" name="value"
                                                    oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                            @endif     
    
                                        </div>
                                        
                                        <div class="m-1 p-0 d-flex">
                                            <button type="submit" class="btn mx-1 mt-2 p-2" id="bid_button" style="text-transform:capitalize; font-size:16px; overflow: hidden;text-overflow: ellipsis;">Place Bid</button>
                                        </div>
                                    </form>
                                </div>
                            @endif

                            <div class="m-auto mt-5 text-center"> 
                                @if (($auction->getHighestBidValue()))
                                    <h4 class="mb-0" id="max-bid"> Current Bid: {{$auction->getHighestBidValue()}}€</h4>  
                                @else
                                    <h4 class="mb-1" id="first-bid">Be The First One To Bid!</h4>
                                @endif
                                <h6 class="text-muted"> 
                                    <span> (Base Value: </span>
                                    <span id="base-value"> {{ $auction->base_value }} €) </span>
                                </h6>  
                            </div>

                            <div class="text-md-start px-1 p-3 m-auto" >
                                <h5 style= "font-weight: bold">
                                    @if (!$auction->end_date and $auction->start_date) 
                                        Auction Cancelled
                                    @elseif ($auction->start_date)
                                        @if (\Carbon\Carbon::now() > $auction->end_date)
                                            Auction Ended
                                        @else
                                            @if ($auction->start_date > \Carbon\Carbon::now()) Starts in
                                            @else Auction Closes in
                                                <span id="time-auction"> --d --h --m --s</span>
                                            @endif
                                        @endif    
                                    @else
                                        Draft
                                    @endif
                                </h5>
                            </div>

                        </div>
                    </div>        
                </div>
            </div>
            <div class="d-flex justify-content-center flex-wrap"> 
                <div class=row> 
                    <div class="col-md-8 auction-description pt-5">
                        <h5 class="text-md-start px-1"><strong>Description:</strong></h5>
                        <h2 class="text-md-start px-1 text-break"> {{$auction->title}}</h2>
                        <h5 class="text-md-start px-1 text-break"> <strong>
                            @foreach ($auction->getCategories() as $category)
                                {{ $category->category }}
                                @if (!$loop->last)
                                    , 
                                @endif
                            @endforeach -
                                {{ $auction->brand ?  $auction->brand : "No brand" }} -
                                {{ $auction->condition ? $auction->condition : "No condition" }} -
                                {{ $auction->year ? $auction->year : "Unknown Year"}}
                        </strong></h5>
                        <span class="text-md-start text-break">  {{ $auction->description }} </span>
                        @if (!Auth::guest())
                            <div class="auction-comments mt-5 pt-5">
                                <h5 class="text-md-start px-1"><strong>Comments:</strong></h5>

                                <div class="container-fluid display-comment overflow-auto">
                                    @if (isset($comments))
                                    @foreach($comments as $comment)
                                        <div class="row">
                                            <div class="col">
                                                <p class="m-2">
                                                    <a href="{{ url('/users/' . $comment->getUser()->id) }}"><strong>{{ $comment->getUser()->username }}</strong></a>
                                                    {{ $comment->message }}
                                                </p>
                                            </div>  
                                            @if ($comment->user_id == Auth::id() or Auth::user()->isadmin)
                                                <form method="post" action="{{ route('comments.user.delete', ['id' => $auction->id, 'comment_id' => $comment->id] ) }}" class="float-right" style="display: contents;">
                                                    @csrf 
                                                    <button type="submit" class="btn btn-outline-danger btn-sm mt-2"  >
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                            <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                                        </svg>
                                                    </button>  
                                                </form>    
                                            @endif
                                        </div>
                                            
                                    @endforeach
                                    @endif
                                </div>

                                <form method="post" action="{{ route('comments.store', $auction->id) }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col">
                                            <div class="form-group">
                                                <input class="form-control h-25 mt-3" type="text" name="comment" placeholder="Comment...">
                                            </div>
                                        </div>

                                        <div class="float-right"  style="display: contents;">
                                            <div class="form-group mt-2">
                                                <input type="submit" class="btn btn-primary btn-sm mt-1" value="Reply"/>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        @endif
                    </div>
                    @if (!Auth::guest())
                        <div class="bidding-history-container col-md-4 pt-5 d-flex flex-column justify-content-start mx-auto">
                            <div class="mx-auto text-center"> 
                                <h4 style="font-weight: bold" > Bidding History </h4>
                            </div>

                            <div class="container-fluid overflow-auto m-auto mt-3 pb-5">
                                @foreach ($auction->getBidHistory() as $bid)
                                    <div class="row mb-3">
                                        <div class="col-md fw-bold d-flex justify-content-center">
                                            <a href="{{ url('/users/' . $bid[0]->id) }}">
                                                {{ $bid[0]->username }}
                                            </a>
                                        </div>
                                        <div class="col-md d-flex justify-content-center">
                                            {{ \Carbon\Carbon::parse($bid[1])->diffForHumans() }}
                                        </div>
                                        <div class="col-md d-flex justify-content-center">
                                            {{ $bid[2] }} €
                                        </div>

                                    </div>
                                    @if ($loop->index == 9)
                                        <div class="row mb-3 d-flex justify-content-center">...</div>
                                        @break
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection