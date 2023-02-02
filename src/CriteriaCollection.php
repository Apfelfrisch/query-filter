<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Criterias\AllowField;
use Apfelfrisch\QueryFilter\Criterias\Criteria;
use Apfelfrisch\QueryFilter\Criterias\Filter;
use Apfelfrisch\QueryFilter\Criterias\Sorting;
use Apfelfrisch\QueryFilter\Exceptions\CriteriaException;
use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<string, Criteria>
 */
final class CriteriaCollection implements IteratorAggregate
{
    /** @var array<string, AllowField> */
    private array $selectFields = [];

    /** @var array<string, Filter> */
    private array $filters = [];

    /** @var array<string, Sorting> */
    private array $sorts = [];

    public function __construct(Criteria ...$criterias)
    {
        foreach ($criterias as $criteria) {
            $this->add($criteria);
        }
    }

    public function add(Criteria $criteria): self
    {
        match (true) {
            $criteria instanceof AllowField => $this->selectFields[$criteria->getName()] = $criteria,
            $criteria instanceof Sorting => $this->sorts[$criteria->getName()] = $criteria,
            $criteria instanceof Filter => $this->filters[$criteria->getName()] = $criteria,
            default => throw new CriteriaException('Unsupported criteria type [' . $criteria::class . ']'),
        };

        return $this;
    }

    public function hasFilter(string $name): bool
    {
        return array_key_exists($name, $this->filters);
    }

    public function hasSorting(string $name): bool
    {
        return array_key_exists($name, $this->sorts);
    }

    public function hasAllowField(string $name): bool
    {
        return array_key_exists($name, $this->selectFields);
    }

    public function getFilter(string $name): Filter
    {
        if (! $this->hasFilter($name)) {
            throw CriteriaException::missingFilter($name);
        }

        return $this->filters[$name];
    }

    public function getSorting(string $name): Sorting
    {
        if (! $this->hasSorting($name)) {
            throw CriteriaException::missingSorting($name);
        }

        return $this->sorts[$name];
    }

    public function getAllowField(string $name): AllowField
    {
        if (! $this->hasAllowField($name)) {
            throw CriteriaException::missingAllowField($name);
        }

        return $this->selectFields[$name];
    }

    public function onlyAllowFields(): self
    {
        $instance = clone $this;

        $instance->filters = [];
        $instance->sorts = [];

        return $instance;
    }

    public function onlyFilters(): self
    {
        $instance = clone $this;

        $instance->selectFields = [];
        $instance->sorts = [];

        return $instance;
    }

    public function onlySorts(): self
    {
        $instance = clone $this;

        $instance->filters = [];
        $instance->selectFields = [];

        return $instance;
    }

    public function merge(self ...$criteriaCollections): self
    {
        $instance = clone $this;

        foreach ($criteriaCollections as $criteriaCollection) {
            $instance->sorts = array_merge($instance->sorts, $criteriaCollection->sorts);
            $instance->filters = array_merge($instance->filters, $criteriaCollection->filters);
            $instance->selectFields = array_merge($instance->selectFields, $criteriaCollection->selectFields);
        }

        return $instance;
    }

    /** @return ArrayIterator<string, AllowField|Filter|Sorting> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(
            array_merge($this->selectFields, $this->filters, $this->sorts)
        );
    }

    /**
     * @template TAdaptable
     * @param QueryBuilder<TAdaptable> $builder
     * @return QueryBuilder<TAdaptable>
     */
    public function applyOn(QueryBuilder $builder): QueryBuilder
    {
        array_walk($this->selectFields, fn (AllowField $criteria): QueryBuilder => $criteria->apply($builder));
        array_walk($this->filters, fn (Filter $criteria): QueryBuilder => $criteria->apply($builder));
        array_walk($this->sorts, fn (Sorting $criteria): QueryBuilder => $criteria->apply($builder));

        return $builder;
    }
}
