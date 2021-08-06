<?php

namespace Leve\Cacheable\Concerns;

use Leve\Cacheable\Models\Item;
use Throwable;

trait Utils
{
    /**
     * @param string $template
     * @param mixed ...$args
     * @return string
     */
    public function resolveCacheName(...$args): string
    {
        try {
            $base_name = config('model_cached.cache_name');
            $params = [$base_name, ...$args];
            $name = trim(str_repeat('%s.', count($params)), '.');

            return sprintf($name, ...$params);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return mixed
     */
    public function cache()
    {
        return new class () {
            /**
             * @param string $key
             * @param mixed $value
             * @param boolean $force
             * @return mixed
             */
            public function put(string $key, mixed $value, bool $force = false): mixed
            {
                if ($force) {
                    $this->forget($key);
                }

                if ($this->exists($key)) {
                    return Item::where('index', $key)->update(['value' => $value]);
                }

                return app(Item::class)->add(['index' => $key, 'value' => $value]);
            }

            /**
             * @param string $key
             * @param mixed $index
             * @return void
             */
            public function add(string $key, $index)
            {
                $item = Item::where('index', $key)->first();
                $item->setConnection('mongodb');
                $item->value = array_values(array_unique([...$item->value ?? [], $index]));
                $item->save();
            }

            /**
             * @param string $key
             * @return void
             */
            public function get(string $key)
            {
                return Item::where('index', $key)->first();
            }

            /**
             * @param string $key
             * @return void
             */
            public function forget(string $key, bool $all = false)
            {
                if ($all) {
                    return Item::where('index', 'regexp', sprintf("/$key.%s/i", '*'))->delete();
                }

                return Item::where('index', '=', $key)->delete();
            }

            /**
             * @param string $key
             * @return boolean
             */
            public function exists(string $key): bool
            {
                return app(Item::class)->exists($key);
            }
        };
    }
}
