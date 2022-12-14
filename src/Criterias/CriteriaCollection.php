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

    public function has(string $name, Type|null $type = null): bool
    {
        $criteria = $this->criterias[$name] ?? null;

        if ($criteria === null) {
            return false;
        }

        if ($type !== null && $criteria->getType() !== $type) {
            return false;
        }

        return true;
    }

    public function hasFilter(string $name): bool
    {
        return $this->has($name, Type::Filter);
    }

    public function hasSort(string $name): bool
    {
        return $this->has($name, Type::Sort);
    }

    public function get(string $name, Type|null $type = null): Criteria
    {
        $criteria = $this->criterias[$name] ?? null;

        if ($criteria !== null) {
            if ($type === null || $criteria->getType() === $type) {
                return $criteria;
            }
        }

        throw new Exception("Criteria with name [$name] not found.");
    }

    public function getFilter(string $name): MutableCriteria
    {
        /** @var MutableCriteria */
        return $this->get($name, Type::Filter);
    }

    public function onlyFilters(): self
    {
        return new self(
            ...array_filter($this->criterias, fn (Criteria $criteria): bool => $criteria->getType() === Type::Filter)
        );
    }

    public function onlySorts(): self
    {
        return new self(
            ...array_filter($this->criterias, fn (Criteria $criteria): bool => $criteria->getType() === Type::Sort)
        );
    }

    public function getSort(string $name): Sort
    {
        /** @var Sort */
        return $this->get($name, Type::Sort);
    }

    /** @return ArrayIterator<string, Criteria> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->criterias);
    }

    public function applyOn(QueryBuilder $builder): QueryBuilder
    {
        array_walk(
            $this->criterias,
            fn (Criteria $criteria): QueryBuilder => $criteria->apply($builder)
        );

        return $builder;
    }
}
