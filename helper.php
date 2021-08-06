<?php

use Carbon\Carbon;
use Illuminate\Container\Container;

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $abstract
     * @param  array  $parameters
     * @return mixed|\Illuminate\Contracts\Foundation\Application
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($abstract, $parameters);
    }
}

if (!function_exists('config_path')) {
    /**
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->configPath($path);
    }
}

if (!function_exists('now')) {
    /**
     * @return Carbon
     */
    function now(): Carbon
    {
        return Carbon::now();
    }
}

if (!function_exists('cacheable_tag_name')) {
    /**
     * Resolve tag name by model
     *
     * @param string $class_name
     * @return string
     */
    function cacheable_tag_name(string $class_name) : string
    {
        $parts = explode('\\', $class_name);

        return strtolower(end($parts));
    }
}
