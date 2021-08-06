<?php

namespace Leve\Cacheable\Facades;

use Illuminate\Support\Facades\Facade;
use Leve\Cacheable\Strategy;
use Leve\Cacheable\Index;

/**
 * @method static Strategy add(string $class, array $options = [])
 * @method static Index index(string $class)
 * @method static array getModels()
 * @method static void register()
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
