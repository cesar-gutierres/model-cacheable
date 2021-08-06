<?php

namespace Leve\Cacheable\Models;

use Jenssegers\Mongodb\Eloquent\Model as BaseModel;

class Model extends BaseModel
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
    protected $table = 'cacheable_models';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['class', 'tag', 'options'];

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

    /**
     * @param string $class
     * @param array $data
     * @return $this
     */
    public function sync(string $class, $data = [])
    {
        $item = $this->where('class', $class)->first() ?? $this->newInstance();
        $item->setConnection("mongodb");
        $item->class = $class;
        $item->fill($data);

        $item->save();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key)
    {
        return $this->query()->where('index', '=', $key)->count() > 0;
    }
}
