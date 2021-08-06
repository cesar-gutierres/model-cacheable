<?php

namespace Leve\Cacheable;

use Illuminate\Support\Collection;
use Leve\Cacheable\Models\Group as GroupModel;

class Group
{
    private string $name;

    private array $indexes = [];

    private Index $index;

    /**
     * Group constructor.
     * @param string $name
     * @param Index $index
     */
    public function __construct(Index $index, string $name, array $indexes = [])
    {
        $this->name = $name;
        $this->index = $index;
        $this->indexes = $indexes;
        $this->refresh();
    }

    /**
     * @return void
     */
    public function refresh()
    {
        $row = GroupModel::firstOrNew(['name' => $this->getName()]);
        $row->setConnection("mongodb");
        $row->indexes = $this->getIndexes();
        $row->expires_in = now()->addMonth();
        $row->save();
    }

    /**
     * @return boolean
     */
    public function has(): bool
    {
        return GroupModel::where('name', $this->getName())->exists();
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return GroupModel::where('name', $this->getName())->first();
    }

    /**
     * Retrieve elements in group
     *
     * @return Collection
     */
    public function retrieve(): Collection
    {
        $items = [];

        $this->indexes = $this->get()?->indexes ?? [];

        foreach ($this->indexes as $index) {
            $items[] = $this->index->get($index, true);
        }

        return collect(array_filter($items));
    }

    /**
     * Clear indexes group
     *
     * @return mixed
     */
    public function flush()
    {
        return $this->get()->setConnection('mongodb')->delete();
    }

    /**
     * Get group name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get indexes in group
     *
     * @return array
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }
}
