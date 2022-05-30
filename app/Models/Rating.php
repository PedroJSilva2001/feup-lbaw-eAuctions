<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = 'rating';

    protected $fillable = [
        'rater_id',
        'rated_id',
        'score',
    ];

    protected $primaryKey = [
        'rater_id',
        'rated_id'
    ];

    public $incrementing = false;

    /**
     * Gets the rated user.
     *
     * @return BelongsTo
     */
    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_id');
    }

    /**
     * Gets the user who rated.
     *
     * @return BelongsTo
     */
    public function raterUser()
    {
        return $this->belongsTo(User::class, 'rate_id');
    }

    /**
     * Set the keys for a save update query.
     *
     * From https://stackoverflow.com/a/37076437/17810802
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * From https://stackoverflow.com/a/37076437/17810802
     * 
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * From https://stackoverflow.com/a/37076437/17810802
     * 
     * @return void
     */
    protected function runSoftDelete()
    {
        $query = $this->newQueryWithoutScopes()->where($this->getKeyName()[0], $this->attributes[$this->getKeyName()[0]])
            ->where($this->getKeyName()[1], $this->attributes[$this->getKeyName()[1]]);

        $time = $this->freshTimestamp();

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->timestamps && !is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        $query->update($columns);
    }
}
