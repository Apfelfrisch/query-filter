<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

use Apfelfrisch\QueryFilter\Criterias\CriteriaCollection;
use Apfelfrisch\QueryFilter\Criterias\Filter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;
use Apfelfrisch\QueryFilter\Criterias\Sort;

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

    public function allowSorts(string|Sort ...$sorts): self
    {
        foreach ($sorts as $sort) {
            $this->allowedSorts->add(
                $sort instanceof Sort ? $sort : new Sort($sort)
            );
        }

        return $this;
    }

    public function getCriterias(string $queryString): CriteriaCollection
    {
        return $this->settings
            ->getQueryParser()
            ->setQueryString($queryString)
            ->parse($this->allowedFilters, $this->allowedSorts);
    }

    public function parse(string $queryString, object $builder): object
    {
        $this->getCriterias($queryString)->applyOn(
            $this->settings->adaptQueryBuilder($builder)
        );

        return $builder;
    }
}
