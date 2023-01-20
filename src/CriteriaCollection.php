<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Criterias\Criteria;
use Apfelfrisch\QueryFilter\Criterias\Filter;
use Apfelfrisch\QueryFilter\Criterias\Sorting;
use Apfelfrisch\QueryFilter\Exceptions\CriteriaException;
use ArrayIterator;
use IteratorAggregate;

/**
 * @template T of Criteria
 *
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

    /** @return $this */
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
            throw CriteriaException::missingCriteria($name);
        }

        return $criteria;
    }

    public function getFilter(string $name): Filter
    {
        if (! $this->hasFilter($name)) {
            throw CriteriaException::missingFilter($name);
        }

        /** @var Filter */
        return $this->get($name);
    }

    public function getSorting(string $name): Sorting
    {
        if (! $this->hasSorting($name)) {
            throw CriteriaException::missingSorting($name);
        }

        /** @var Sorting */
        return $this->get($name);
    }

    /** @return self<Filter> */
    public function onlyFilters(): self
    {
        /** @var self<Filter> */
        return new self(
            ...array_filter($this->criterias, fn (Criteria $criteria): bool => $criteria instanceof Filter)
        );
    }

    /** @return self<Sorting> */
    public function onlySorts(): self
    {
        /** @var self<Sorting> */
        return new self(
            ...array_filter($this->criterias, fn (Criteria $criteria): bool => $criteria instanceof Sorting)
        );
    }

    /**
     * @template TMerge of Criteria
     * @param self<TMerge> $criteriaCollection
     * @return self<Criteria>
     */
    public function merge(self $criteriaCollection): self
    {
        return new self(
            ...array_merge($this->criterias, $criteriaCollection->criterias)
        );
    }

    /** @return ArrayIterator<string, Criteria> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->criterias);
    }

    /**
     * @template TAdaptable
     * @param QueryBuilder<TAdaptable> $builder
     * @return QueryBuilder<TAdaptable>
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
