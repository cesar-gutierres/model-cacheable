<?php

namespace Leve\Cacheable;

use Leve\Cacheable\Models\Model;

class Strategy
{
    protected array $models = [];

    public function __construct(private Model $eloquent)
    {
    }

    /**
     * @param string $class
     * @return $this
     */
    public function add(string $class, array $options = [])
    {
        $tag = cacheable_tag_name($class);

        $this->eloquent->sync($class, ['tag' => $tag, 'options' => $options]);

        $this->models[$class] = new Index($tag, $class, $options);

        return $this;
    }

    /**
     * @return void
     */
    public function register(): void
    {
        foreach ($this->models as $class => $index) {
            $class::crape(true);
        }
    }

    /**
     * Get models register
     *
     * @return array
     */
    public function getModels(): array
    {
        return $this->models;
    }

    /**
     * @param string $class
     * @return Index
     */
    public function index(string $class): Index
    {
        return $this->models[$class];
    }
}
