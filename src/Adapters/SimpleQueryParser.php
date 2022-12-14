<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Adapters;

use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Criterias\CriteriaCollection;
use Apfelfrisch\QueryFilter\QueryBag;
use Apfelfrisch\QueryFilter\QueryParser;
use Exception;

final class SimpleQueryParser implements QueryParser
{
    private QueryBag $query;

    /** @param non-empty-string $delimter */
    public function __construct(
        private string $keywordFilter = 'filter',
        private string $keywordSort = 'sort',
        private string $delimter = ',',
    ) {
        $this->query = new QueryBag();
    }

    public function setQuery(QueryBag $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function parse(
        CriteriaCollection $allowedFilters,
        CriteriaCollection $allowedSorts = new CriteriaCollection
    ): CriteriaCollection {
        $appliedCriterias = new CriteriaCollection();

        $this->parseFilters($allowedFilters, $appliedCriterias);

        $this->parseSorts($allowedSorts, $appliedCriterias);

        return $appliedCriterias;
    }

    private function parseFilters(CriteriaCollection $allowedFilters, CriteriaCollection $appliedCriterias): void
    {
        $queryStringFilters = $this->query->getArray($this->keywordFilter);

        foreach ($queryStringFilters as $filtername => $filterString) {
            if (! is_string($filtername)) {
                throw new Exception("Query String not well formed.");
            }

            if (! $allowedFilters->hasFilter($filtername)) {
                throw new Exception("Filter [$filtername] not allowd.");
            }

            $values = $this->getValues($filterString);

            if (is_string($values) && $values === '') {
                continue;
            }

            $filter = $allowedFilters->getFilter($filtername);
            $filter->setValue($this->getValues($filterString));

            $appliedCriterias->add($filter);
        }
    }

    private function parseSorts(CriteriaCollection $allowedSorts, CriteriaCollection $appliedCriterias): void
    {
        $values = $this->getValues($this->query->getString($this->keywordSort));

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

            if (! $allowedSorts->hasSort($value)) {
                throw new Exception("Sort over [$value] is not allowd.");
            }

            $sortCriteria = $allowedSorts->getSort($value);
            $sortCriteria->setSortDirection($sortDirection);

            $appliedCriterias->add($sortCriteria);
        }
    }

    /** @return string|array<int, string> */
    private function getValues(mixed $filterString): array|string
    {
        if (! is_string($filterString)) {
            throw new Exception("Query String not well formed.");
        }

        if (! str_contains($filterString, $this->delimter)) {
            return trim($filterString);
        }

        return array_map(static function (string $value): string {
            return trim($value);
        }, explode($this->delimter, $filterString));
    }
}
