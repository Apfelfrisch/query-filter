<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\QueryBuilder;
use ArrayIterator;
use Exception;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<string, Criteria>
 */
final class CriteriaCollection implements IteratorAggregate
{
    /** @var array<string, Criteria> */
    private array $criterias = [];

    public function __construct(Criteria ...$criterias)
    {
        foreach ($criterias as $criteria) {
            $this->add($criteria);
        }
    }

    public function add(Criteria $criteria): self
    {
        $this->criterias[$criteria->getName()] = $criteria;

        return $this;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->criterias);
    }

    public function hasFilter(string $name): bool
    {
        if (! $this->has($name)) {
            return false;
        }

        return $this->get($name) instanceof Filter;
    }

    public function hasSorting(string $name): bool
    {
        if (! $this->has($name)) {
            return false;
        }

        return $this->get($name) instanceof Sorting;
    }

    public function get(string $name): Criteria
    {
        $criteria = $this->criterias[$name] ?? null;

        if ($criteria === null) {
            throw new Exception("Criteria with name [$name] not found.");
        }

        return $criteria;
    }

    public function getFilter(string $name): Filter
    {
        if (! $this->hasFilter($name)) {
            throw new Exception("Filter with name [$name] not found.");
        }

        /** @var Filter */
        return $this->get($name);
    }

    public function getSorting(string $name): Sorting
    {
        if (! $this->hasSorting($name)) {
            throw new Exception("Sorting with name [$name] not found.");
        }

        /** @var Sorting */
        return $this->get($name);
    }

    public function onlyFilters(): self
    {
        return new self(
            ...array_filter($this->criterias, fn (Criteria $criteria): bool => $criteria instanceof Filter)
        );
    }

    public function onlySorts(): self
    {
        return new self(
            ...array_filter($this->criterias, fn (Criteria $criteria): bool => $criteria instanceof Sorting)
        );
    }

    /** @return ArrayIterator<string, Criteria> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->criterias);
    }

    /**
     * @template T
     * @param QueryBuilder<T> $builder
     * @return QueryBuilder<T>
     */
    public function applyOn(QueryBuilder $builder): QueryBuilder
    {
        array_walk(
            $this->criterias,
            fn (Criteria $criteria): QueryBuilder => $criteria->apply($builder)
        );

        return $builder;
    }
}
