<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\Criterias\Filter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;
use Apfelfrisch\QueryFilter\Criterias\Sorting;

final class QueryFilter
{
    private CriteriaCollection $allowedFilters;
    private CriteriaCollection $allowedSorts;

    public function __construct(
        private Settings $settings = new Settings(),
    ) {
        $this->allowedFilters = new CriteriaCollection();
        $this->allowedSorts = new CriteriaCollection();
    }

    public static function new(
        Settings $settings = new Settings(),
    ): self {
        return new self($settings);
    }

    public function allowFilters(string|Filter ...$filters): self
    {
        foreach ($filters as $filter) {
            $this->allowedFilters->add(
                $filter instanceof Filter ? $filter : new PartialFilter($filter)
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

    /** @param QueryBag|array<mixed>|null $queryParameters */
    public function getCriterias(QueryBag|array|null $queryParameters = null): CriteriaCollection
    {
        if (! $queryParameters instanceof QueryBag) {
            $queryParameters = new QueryBag($queryParameters ?? $_GET);
        }

        return $this->settings
            ->getQueryParser()
            ->setQuery($queryParameters)
            ->parse($this->allowedFilters, $this->allowedSorts);
    }

    /** @param QueryBag|array<mixed>|null $queryParameters */
    public function applyOn(object $builder, QueryBag|array|null $queryParameters = null): object
    {
        $this->getCriterias($queryParameters)->applyOn(
            $this->settings->adaptQueryBuilder($builder)
        );

        return $builder;
    }
}
