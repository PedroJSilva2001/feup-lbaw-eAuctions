<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'image';

    protected $fillable = [
        'path',
        'auction_id'
    ];

    /**
     * Gets the auction of the image.
     *
     * @return BelongsTo
     */
    public function referedAuction()
    {
        $this->belongsTo(Auction::class);
    }
}
