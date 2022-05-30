<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Transaction extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'transaction';

    protected $fillable = [
      'user_id',
      'value',
      'description',
      'date',
      'method',
      'status'
    ];

    /**
     * Gets the user of the transaction.
     *
     * @return BelongsTo
     */
    public function referedUser()
    {
      return $this->belongsTo(User::class);
    }
}
