<?php

namespace Leve\Cacheable\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Cacheable extends Model
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
    protected $table = 'cacheable';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['index', 'value', 'group'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

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

    public function exists(string $key)
    {
        // $this->setConnection('mongodb');

        return $this->query()->where('index', '=', $key)->count() > 0;
    }
}
