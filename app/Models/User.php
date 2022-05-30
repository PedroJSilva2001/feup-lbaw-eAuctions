<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \Carbon\Carbon;

use App\Models\Auction;
use App\Models\Bid;

class User extends Authenticatable
{
    use Notifiable;
    public $table = 'user';
    public $timestamps  = false;
    public $fillable = [
        'username',
        'name',
        'email',
        'password',
        'credit',
        'picture',
        'rating',
        'isAdmin'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'username' => 'string',
        'email' => 'string',
        'name' => 'string',
        'password' => 'string',
        'credit' => 'float',
        'picture' => 'string',
        'rating' => 'float',
        'isAdmin' => 'boolean'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [];

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function personalAuctions()
    {
        return $this->hasMany(Auction::class);
    }

    public function followedAuctions()
    {
        return $this->hasMany(Follow::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class, 'user_id');
    }

    public function adminBlocks()
    {
        return $this->hasMany(Block::class, 'admin_id');
    }

    /**
     * Checks if user is admin.
     * 
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * Gets the bids of the user.
     * 
     * @return Collection
     */
    public function getBids()
    {
        return Bid::where('user_id', '=', $this->id)->get();
    }

    /**
     * Gets information of the bidding hitory of the user.
     * 
     * @return array
     */
    public function getBidHistory()
    {
        $result = [];
        $bids = $this->getBids();
        foreach ($bids as $bid) {
            $auction = Auction::where('id', '=', $bid->auction_id)->first();
            array_push($result, [$auction, $bid->value, Carbon::parse($bid->date)->format('Y-m-d H:i:s')]);
        }
        usort($result, fn ($a, $b) => strtotime($b[2]) - strtotime($a[2]));
        return $result;
    }

    /**
     * Checks if user is following auction with given id.
     * 
     * @param int $auction_id
     * @return bool
     */
    public function isFollowingAuction($auction_id)
    {
        return (Follow::where('user_id', '=', $this->id)->where('auction_id', '=', $auction_id)->first() != null);
    }

    /**
     * Checks if user has bid on auction with given id.
     * 
     * @param int $auction_id
     * @return bool
     */
    public function hasBidAuction($auction_id)
    {
        return (Bid::where('user_id', '=', $this->id)->where('auction_id', '=', $auction_id)->first() != null);
    }

    /**
     * Gets user's most recent block.
     * 
     * @return Block
     */
    public function mostRecentBlock() {
        return $this->blocks()
                    ->orderBy('start_date', 'desc')
                    ->get()->first();     
    }

    /**
     * Checks if user is blocked.
     * 
     * @return bool
     */
    public function isBlocked() {
        $block = Block::where('user_id', $this->id)->first();

        if ($block == null) {
            return false;
        }

        // Permantly blocked
        if ($block->end_date == null) {
            return true;
        }

        return $block->end_date > Carbon::now();
    }

    /**
     * Checks if user has unread notifications.
     * 
     * @return bool
     */
    public function hasUnreadNotifications(){
        $unreadNotifications = Notification::where('user_id', $this->id)->where('seen', false);
        return count($unreadNotifications->get()) > 0;
    }

    /**
     * Cancels all auctions of user.
     */
    public function cancelAllAuctions() {
        $auctions = Auction::where("seller_id", "=", $this->id)->get();

        foreach ($auctions as $auction) {
            $auction->end_date = null;
            $auction->save();

            // Notify followers
            $followers = User::wherein('id', 
                                Follow::where("auction_id", $auction->id)->get()->map->only(['user_id'])
                            )->orWherein('id', Auction::where('id', $auction->id)->get()->map->only(['seller_id']))->get();

            foreach ($followers as $follower) {
                $notification =  new Notification([
                    'user_id' => $follower->id,
                    'auction_id' => $auction->id,
                    'date' => Carbon::now(),
                    'type' => 'Auction Cancelled',
                ]);
                $notification->save();
            }
        }
    }
}
