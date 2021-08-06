<?php

namespace Leve\Cacheable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @static \Leve\Cacheable\Index add(string $class, array $options = [])
 * @static \Leve\Cacheable\Index index(string $class)
 * @static array getModels()
 * @static void register()
 */
class Cacheable extends Facade 
{
    /**
     * @return mixed
     */
    protected static function getFacadeAccessor()
    {
        return 'cacheable';
    }
}