<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Adapters;

use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\Exceptions\CriteriaException;
use Apfelfrisch\QueryFilter\Exceptions\QueryStringException;
use Apfelfrisch\QueryFilter\QueryBag;
use Apfelfrisch\QueryFilter\QueryParser;

final class SimpleQueryParser implements QueryParser
{
    /** @phpstan-param non-empty-string $delimter */
    public function __construct(
        private string $keywordFilter = 'filter',
        private string $keywordSort = 'sort',
        private string $delimter = ',',
    ) {
    }

    public function parse(
        QueryBag $query,
        CriteriaCollection $allowedFilters = new CriteriaCollection,
        CriteriaCollection $allowedSorts = new CriteriaCollection,
    ): CriteriaCollection {
        return $this->parseFilters($query, $allowedFilters)
            ->merge($this->parseSorts($query, $allowedSorts));
    }

    private function parseFilters(QueryBag $query, CriteriaCollection $allowedFilters): CriteriaCollection
    {
        $appliedCriterias = new CriteriaCollection();

        $queryStringFilters = $query->getArray($this->keywordFilter);

        foreach ($queryStringFilters as $filtername => $filterString) {
            if (! is_string($filtername)) {
                throw QueryStringException::unparseableQueryString();
            }

            if (! $allowedFilters->hasFilter($filtername)) {
                throw CriteriaException::forbiddenFilter($filtername, $allowedFilters);
            }

            $values = $this->getValues($filterString);

            if (is_string($values) && $values === '') {
                continue;
            }

            $filter = $allowedFilters->getFilter($filtername);
            $filter->setValue($this->getValues($filterString));

            $appliedCriterias->add($filter);
        }

        return $appliedCriterias;
    }

    private function parseSorts(QueryBag $query, CriteriaCollection $allowedSorts): CriteriaCollection
    {
        $appliedCriterias = new CriteriaCollection();

        $values = $this->getValues($query->getString($this->keywordSort));

        if (is_string($values)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            if ($value === '') {
                continue;
            }

            if ($value[0] === '-') {
                $value = substr($value, 1);
                $sortDirection = SortDirection::Descending;
            } else {
                $sortDirection = SortDirection::Ascending;
            }

            if (! $allowedSorts->hasSorting($value)) {
                throw CriteriaException::forbiddenSorting($value, $allowedSorts);
            }

            $sortCriteria = $allowedSorts->getSorting($value);
            $sortCriteria->setSortDirection($sortDirection);

            $appliedCriterias->add($sortCriteria);
        }

        return $appliedCriterias;
    }

    /** @return string|array<int, string> */
    private function getValues(mixed $filterString): array|string
    {
        if (! is_string($filterString)) {
            throw QueryStringException::unparseableQueryString();
        }

        if (! str_contains($filterString, $this->delimter)) {
            return trim($filterString);
        }

        return array_map(static function (string $value): string {
            return trim($value);
        }, explode($this->delimter, $filterString));
    }
}
