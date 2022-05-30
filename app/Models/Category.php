<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Auction;

class Category extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'category_auction';

    protected $fillable = [
        'auction_id',
        'category',
    ];

    protected $primaryKey = [
        'auction_id',
        'category'
    ];

    public $incrementing = false;

    /**
     * Gets the auction of the category-auction relation.
     *
     * @return BelongsTo
     */
    public function referedAuction()
    {
        $this->belongsTo(Auction::class);
    }

    /**
     * Gets the number of auctions of a category.
     * 
     * @return int
     */
    public function getNumberAuctions() {
        return Category::where('category', '=', $this->category)->groupBy('category')->count('auction_id');
    }
}
