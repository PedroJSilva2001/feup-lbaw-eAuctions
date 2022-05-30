<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Category;
use App\Models\Image;
use \Carbon\Carbon;

class Auction extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'auction';

    protected $fillable = [
        'title', 
        'seller_id',
        'description', 
        'brand', 
        'colour',
        'condition',
        'year', 
        'start_date',
        'end_date',
        'base_value', 
        'type'
    ];
    
    public function comments() 
    {
        return $this->hasMany(Comment::class);
    }

    public function bids() 
    {
        return $this->hasMany(Bid::class);
    }

    /**
     * Gets all bids of the auction.
     * 
     * @return Collection
     */
    public function getBids() 
    {
        return Bid::where('auction_id', '=', $this->id)->get();
    }

    /**
     * Gets the total number of bids of the auction.
     * 
     * @return int
     */
    public function bidsCount() 
    {
        return Bid::where('auction_id', '=', $this->id)->count();
    }

    /**
     * Gets information of the bidding hitory of the auction.
     * 
     * @return array
     */
    public function getBidHistory() 
    { 
        $result = [];
        $bids = $this->getBids();
        foreach ($bids as $bid) {
            $user = User::where('id', '=', $bid->user_id)->first();
            array_push($result, [$user, Carbon::parse($bid->date)->format('Y-m-d H:i:s'), $bid->value]);
        }
        usort($result, fn ($a, $b) => strtotime($b[1]) - strtotime($a[1]));
        return $result;
    }
    
    public function images() 
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Gets the total number of images of the auction.
     * 
     * @return int
     */
    public function imagesCount() 
    {
        return Image::where('auction_id', '=', $this->id)->count();
    }

    public function categories() 
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Gets the seller of the auction.
     *
     * @return BelongsTo
     */
    public function seller() 
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Gets the seller of the auction.
     * 
     * @return User
     */
    public function getSeller() 
    {
        return User::where('id', '=', $this->seller_id)->first();
    }

    /**
     * Gets the time value.
     * 
     * @return Carbon
     */
    public function getTimeValue() 
    {
        if ($this->start_date > Carbon::now())
            return Carbon::parse($this->start_date);
        else return Carbon::parse($this->end_date);
    }

    /**
     * Gets a string of a message containing the time difference between the present time and the auctios end date.
     * 
     * @return string
     */
    public function getTimeDifference()
    {
        if ($this->end_date == null && $this->start_date != null)
            return "Cancelled";
        else if ($this->start_date == null)
            return "Draft";
        else if (Carbon::now() > $this->end_date)
            return "Auction Closed " . Carbon::parse($this->end_date)->diffForHumans();
        else if ($this->start_date > Carbon::now())
            return "Starts in " . Carbon::parse($this->start_date)->diffForHumans(); 
        else return "Ends in "  . Carbon::parse($this->end_date)->diffForHumans();
    }

    /**
     * Gets the highest bid value of the auction.
     * 
     * @return float
     */
    public function getHighestBidValue() 
    {
        return Bid::where('auction_id', '=', $this->id)->max('value');
    }

    /**
     * Gets the current highest bid of the auction.
     * 
     * @return Bid
     */
    public function getCurrentHighestBid() 
    {
        return Bid::where([['auction_id', '=', $this->id], ['value','=', $this->getHighestBidValue()],])->first();
    }

    /**
     * Gets the pictures of the auction.
     * 
     * @return Collection
     */
    public function getAuctionPictures() 
    {
        return Image::where('auction_id', '=', $this->id)->get();
    }

    /**
     * Gets information on the pictures of the auction.
     * 
     * @return array
     */
    public function getAuctionPicturesIndex() 
    {
        $result = [];
        $pictures = $this->getAuctionPictures();
        for ($var = 0; $var < $this->imagesCount(); $var++) {
            array_push($result, [$pictures[$var]->path, $var]);
        }
        return $result;
    }

    /**
     * Gets the categories of the auction.
     * 
     * @return Collection
     */
    public function getCategories() 
    {
        return Category::where('auction_id', '=', $this->id)->get();
    }

    /**
     * Gets the number of bids of an auction.
     * 
     * @return int
     */
    public function getNumberBids()
    {
        return Bid::where('auction_id', '=', $this->id)->groupBy('auction_id')->count('value');
    }

    /**
     * Checks if an auction is a draft.
     * 
     * @return bool
     */
    public function isDraft()
    {
        return is_null($this->start_date);
    }

    /**
     * Checks if an auction is cancelled.
     * 
     * @return bool
     */
    public function isCancelled()
    {
        return is_null($this->end_date) && !$this->isDraft();
    }

}