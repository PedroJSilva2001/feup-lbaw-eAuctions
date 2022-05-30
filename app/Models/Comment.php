<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'comment';

    protected $fillable = [
        'user_id',
        'auction_id',
        'message',
        'date'
    ];

    /**
     * Gets the auction which was commented on.
     *
     * @return BelongsTo
     */
    public function referedAuction()
    {
        return $this->belongsTo(Auction::class);
    }

    /**
     * Gets the owner of the comment.
     *
     * @return BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gets the user who made the comment.
     * 
     * @return User
     */
    public function getUser() 
    {
        return User::where('id', '=', $this->user_id)->first();
    }
}
