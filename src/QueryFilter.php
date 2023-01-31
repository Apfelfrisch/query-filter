<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Criterias\AllowField;
use Apfelfrisch\QueryFilter\Criterias\Criteria;
use Apfelfrisch\QueryFilter\Criterias\Filter;
use Apfelfrisch\QueryFilter\Criterias\Sorting;

final class QueryFilter
{
    private bool $skipForbiddenCriterias;

    /** @var CriteriaCollection<Criteria> */
    private CriteriaCollection $defaultCriterias;
    /** @var CriteriaCollection<Filter> */
    private CriteriaCollection $allowedFilters;
    /** @var CriteriaCollection<Sorting> */
    private CriteriaCollection $allowedSorts;

    public function __construct(
        private Settings $settings = new Settings(),
    ) {
        $this->defaultCriterias = new CriteriaCollection();
        $this->allowedFilters = new CriteriaCollection();
        $this->allowedSorts = new CriteriaCollection();
        $this->skipForbiddenCriterias = $settings->skipForbiddenCriterias();
    }

    public static function new(
        Settings $settings = new Settings(),
    ): self {
        return new self($settings);
    }

    public function skipForbiddenCriterias(bool $skip = true): self
    {
        $this->skipForbiddenCriterias = $skip;

        return $this;
    }

    public function addDefaultCriterias(Criteria ...$criterias): self
    {
        foreach ($criterias as $criteria) {
            $this->defaultCriterias->add($criteria);
        }

        return $this;
    }

    public function allowFields(string|AllowField ...$allowFields): self
    {
        foreach ($allowFields as $allowField) {
            $this->defaultCriterias->add(
                $allowField instanceof AllowField ? $allowField : new AllowField($allowField)
            );
        }

        return $this;
    }

    public function allowFilters(string|Filter ...$filters): self
    {
        $defaultFilter = $this->settings->getDefaultFilterClass();

        foreach ($filters as $filter) {
            $this->allowedFilters->add(
                $filter instanceof Filter ? $filter : new $defaultFilter($filter)
            );
        }

        return $this;
    }

    public function allowSorts(string|Sorting ...$sorts): self
    {
        foreach ($sorts as $sort) {
            $this->allowedSorts->add(
                $sort instanceof Sorting ? $sort : new Sorting($sort)
            );
        }

        return $this;
    }

    /**
     * @param QueryBag|array<mixed>|null $queryParameters
     * @return CriteriaCollection<Criteria>
     */
    public function getCriterias(QueryBag|array|null $queryParameters = null): CriteriaCollection
    {
        if (! $queryParameters instanceof QueryBag) {
            $queryParameters = new QueryBag($queryParameters ?? $_GET);
        }

        return $this->defaultCriterias->merge(
            $this->settings
            ->getQueryParser()
            ->skipForbiddenCriterias($this->skipForbiddenCriterias)
            ->parse($queryParameters, $this->allowedFilters, $this->allowedSorts)
        );
    }

    /**
     * @template T of object
     *
     * @param T $builder
     * @param QueryBag|array<mixed>|null $queryParameters
     * @return T
     */
    public function applyOn(object $builder, QueryBag|array|null $queryParameters = null): object
    {
        $this->getCriterias($queryParameters)->applyOn(
            $this->settings->adaptQueryBuilder($builder)
        );

        return $builder;
    }

    public function __clone(): void
    {
        $this->allowedSorts = clone $this->allowedSorts;
        $this->allowedFilters = clone $this->allowedFilters;
        $this->defaultCriterias = clone $this->defaultCriterias;
    }
}
