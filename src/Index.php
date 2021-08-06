<?php

namespace Leve\Cacheable;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Leve\Cacheable\Concerns\TTL;
use Leve\Cacheable\Concerns\Utils;
use Leve\Cacheable\Models\Model;
use Leve\Cacheable\Models\Group as GroupModel;

class Index
{
    use Utils;
    use TTL;

    private string $tag;

    private ?string $model;

    private ?Collection $options;

    private Collection $groups;

    /**
     * Index constructor.
     * @param string $tag
     * @param string|null $model
     */
    public function __construct(string $tag, ?string $model, ?array $options = [])
    {
        $this->tag = $tag;
        $this->model = $model;
        $this->load();
    }

    /**
     * Load data
     *
     * @return void
     */
    public function load()
    {
        $model = Model::where('tag', $this->tag)->first();

        $this->options = collect($model->options);
        $this->groups = collect();

        $groupTag = $this->getItemCacheName();

        GroupModel::where('name', 'regexp', sprintf("/$groupTag.%s/i", '*'))
            ->get()
            ->each(fn($group) => $this->addGroup($group->key_name, $group->indexes));
    }

    /**
     * Get tag name
     *
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * Heap of indexes
     *
     * @return Collection
     */
    public function heap(): Collection
    {
        return collect($this->get($this->getIndexCacheName(), false));
    }

    /**
     * @param mixed $values
     * @return void
     */
    public function pushAll($values)
    {
        $this->cache()->put($this->getIndexCacheName(), $values);
    }

    /**
     * Add index in heap
     *
     * @param $index
     */
    public function push($index): void
    {
        $this->cache()->add($this->getIndexCacheName(), $index);
    }

    /**
     * Set item
     *
     * @param $item
     */
    public function set($item, bool $serialize = false): void
    {
        $key = $this->getItemCacheName($item->id);

        $this->push($item->id);

        $this->cache()->put($key, $serialize ? serialize($item) : $item);
    }

    /**
     * Retrieve item by key
     *
     * @param $key
     * @return mixed
     */
    public function get($key, bool $unserialize = false)
    {
        if (is_int($key)) {
            $key = $this->getItemCacheName($key);
        }

        if (!$this->cache()->exists($key)) {
            return [];
        }

        $row = $this->cache()->get($key);

        if (is_string($row->value)) {
            return $unserialize ? unserialize($row->value) : $row->value;
        }

        return $row->value;
    }

    /**
     * @param string $key
     * @return void
     */
    public function clear(string $key)
    {
        return $this->cache()->forget($key);
    }

    /**
     * Remove item to heap
     *
     * @param $key
     * @return mixed
     */
    public function forget($key)
    {
        $heap = $this->heap();

        // get index position
        $index = $heap->search($key);

        if ($index) {
            $this->clear($this->getIndexCacheName());
            $heap->splice($index, 1);
            $heap->each(fn($id) => $this->push($id));
        }

        return $this->clear($this->getItemCacheName($key));
    }

    /**
     * Flush cache entries
     *
     * @return mixed
     */
    public function flush()
    {
        // limpar grupos
        $this->getGroups()->each->flush();

        return $this->cache()->forget($this->resolveCacheName($this->getTag()), true);
    }

    /**
     * Get group by name
     *
     * @param string $key
     * @return Group
     */
    public function group(string $key): Group
    {
        return $this->groups[$key];
    }

    /**
     * Add group to index
     *
     * @param string $key
     * @param \Closure|array $value
     * @return Index
     */
    public function addGroup(string $key, $value, $strict = false): Index
    {
        $value = is_callable($value) ? $this->prepareCallable($value) : $value;

        $groupKey = $this->getItemCacheName($key);

        $items = $value instanceof Collection ? $value->toArray() : $value;

        if (!$items && $strict) {
            throw new Exception('não existem indices para serem indexados.');
        }

        $this->groups->put($key, new Group($this, $groupKey, $items));

        return $this;
    }

    /**
     * Get all groups
     *
     * @return Collection
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $key, mixed $default): mixed
    {
        return $this->options->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    /**
     * Get index key
     *
     * @return string
     */
    public function getIndexCacheName()
    {
        return $this->getItemCacheName('indexes');
    }

    /**
     * Get name by key
     *
     * @param $key
     * @return string
     */
    public function getItemCacheName(?string $key = '')
    {
        return $this->resolveCacheName($this->getTag(), $key);
    }

    /**
     * @param callable $fnc
     * @return array
     */
    private function prepareCallable(callable $fnc): array
    {
        $call = call_user_func($fnc, app($this->model));

        $indexes = [];

        if (!$call instanceof Builder) {
            return $indexes;
        }

        $indexes = $call->select('id')->get()->pluck('id')->toArray();

        return  $indexes;
    }
}
