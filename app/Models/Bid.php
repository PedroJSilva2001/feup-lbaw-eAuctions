<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Bid extends Model
{
    public $timestamps  = false;
    protected $table = 'bid';

    protected $fillable = [
        'user_id',
        'auction_id',
        'value',
        'date'
    ];

    /**
     * Gets the bidder of the bid.
     *
     * @return BelongsTo
     */
    public function referedBidder()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gets the auction of the bid.
     *
     * @return BelongsTo
     */
    public function referedAuction()
    {
        return $this->belongsTo(Auction::class);
    }

    /**
     * Gets the total number of bids in the platform.
     * 
     * @return int
     */
    public function getCount()
    {
        return DB::table('bid')->count();
    }
}
