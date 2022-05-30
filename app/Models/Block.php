<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'block';

    protected $fillable = [
        'admin_id',
        'user_id',
        'description',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime'
    ];

    /**
     * Gets the admin who blocked.
     *
     * @return BelongsTo
     */
    public function referedAdmin() {

        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Gets the user who was blocked.
     *
     * @return BelongsTo
     */
    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
