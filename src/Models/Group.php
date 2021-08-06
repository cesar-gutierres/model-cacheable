<?php

namespace Leve\Cacheable\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Group extends Model
{
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cacheable_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'indexes', 'expires_in'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'expires_in'];

    /**
     * @return string
     */
    public function getKeyNameAttribute(): string
    {
        $values = explode('.', $this->name);

        return end($values);
    }

    /**
     * @param array $data
     * @return void
     */
    public function add($data = [])
    {
        $this->setConnection('mongodb');

        $item = $this->newInstance();
        $item->fill($data);
        $item->save();

        return $item;
    }
}
