<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'notification';

    protected $fillable = [
        'user_id',
        'auction_id',
        'bid_id',
        'date',
        'seen',
        'type'
    ];

    /**
     * Gets the auction of the notification.
     *
     * @return BelongsTo
     */
    public function referedAuction()
    {
        return $this->belongsTo(Auction::class);
    }

    /**
     * Gets the user of the notification.
     *
     * @return BelongsTo
     */
    public function referedUser()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gets the bid of the notification.
     *
     * @return BelongsTo
     */
    public function referedBid()
    {
        return $this->belongsTo(Bid::class);
    }

    /**
     * Gets notification's associated auction.
     * 
     * @return Auction
     */
    public function getAssociatedAuction(){
        $auction_id = (is_null($this->auction_id) ? Bid::where('id' , $this->bid_id)->first()->auction_id : $auction_id = $this->auction_id);
        return Auction::where('id', $auction_id)->first();
    }
}
