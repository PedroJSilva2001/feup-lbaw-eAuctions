<div class="mb-3">
    <h4>{{ $type }} </h4>
    @include('partials.auction_card', array(
        'id'            => $auction->id,
        'title'         => $auction->title,
        'base_value'    => $auction->base_value,
        'max_bid'       => $auction->getHighestBidValue(),
        'time_left'     => $auction->getTimeDifference(),
        'product_imgs'  => $auction->getAuctionPictures()
    ))
</div>