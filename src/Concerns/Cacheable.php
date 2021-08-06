<?php

namespace Leve\Cacheable\Concerns;

use Leve\Cacheable\Group;
use Leve\Cacheable\Index;
use Throwable;

trait Cacheable
{
    protected ?Index $strategy;

    /**
     * Observer events
     *
     * @return void
     */
    protected static function bootCacheable(): void
    {
        static::registerModelEvent('saved', __CLASS__ . "@cachePersist");
        static::registerModelEvent('deleted', __CLASS__ . "@dropCached");
    }

    /**
     * inform which model is curly
     *
     * @return void
     */
    public static function crape(bool $register = false): void
    {
        $strategy = self::getCacheIndex();

        if ($register) {
            // push indexes
            $strategy->pushAll(static::all('id')->pluck('id')->toArray());

            // register group default
            $strategy->addGroup('all', $strategy->heap()->toArray());
        }
    }

    /**
     * @return Index
     */
    public static function getCacheIndex(): Index
    {
        return app('cacheable')->index(__CLASS__);
    }

    /**
     * @param $model
     */
    public static function cacheRetrieve($model)
    {
        $strategy = self::getCacheIndex();

        // ignore if exist id in heap.
        if ($strategy->heap()->contains($model->id)) {
            return;
        }

        $strategy->push($model->id);
    }

    /**
     * @param $model
     */
    public function cachePersist($model)
    {
        // clear all
        $this->dropCached($model);

        // persist item
        $this->cached($model);
    }

    /**
     * @param $model
     * @return mixed
     */
    public static function dropCached($model): mixed
    {
        return self::getCacheIndex()->forget($model->id);
    }

    /**
     * @param string $group_name
     * @return Group
     * @throws Throwable
     */
    public static function cache(string $group_name = 'all')
    {
        return self::getCacheIndex()->group($group_name);
    }

    /**
     * Using in collection each->cached()
     *
     * @return void
     */
    public function cached($model = null): void
    {
        $concrete = $model ?? $this;
        $serialize = true;

        if (method_exists($concrete, 'dataMap')) {
            $serialize = false;
            $concrete = (object) $concrete->dataMap();
        }

        self::getCacheIndex()->set($concrete, $serialize);
    }
}
