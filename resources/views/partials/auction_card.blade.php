<div class="col mb-3 card-container">
    <div class="card auction-card">
        
        @if (isset($product_imgs[0]))
            <img src="{{ asset('assets/' . $product_imgs[0]->path) }}" alt="Product" class="img-fluid auction-card-img-thumbnail">
        @else
            <img src="{{ asset('assets/auction_pictures/default/1.jpg') }}" alt="Product" class="img-fluid auction-card-img-thumbnail">
        @endif
        
        <div class="card-body row d-flex justify-content-center text-center">
            <h4 class="card-title text-center text-truncate">{{ $title }}</h4>
            <div class="card-text card-body-info">
                <div class="auction-current-value">
                    <p class="auction-card-price-title"> Current Bid </p>
                    @if (isset($max_bid))
                        <span class="card-price">€ {{ $max_bid }}</span>
                    @else  
                        <span class="card-price"> No Bids</span>
                        <p class="text-muted mt-0 mb-2"> Base Value - {{ $auction->base_value }}  € </p>
                    @endif
                </div>
                <div class="time-remaining-text text-muted">
                    <span> {{ $time_left }} </span>
                </div>
                <a href="{{ url('/auctions/' . $id) }}" class="stretched-link"></a> 
            </div>
        </div>
        
    </div>
</div>