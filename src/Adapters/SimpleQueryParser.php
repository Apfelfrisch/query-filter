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
    private bool $skipForbiddenCriterias = false;

    /** @phpstan-param non-empty-string $delimter */
    public function __construct(
        private string $keywordFilter = 'filter',
        private string $keywordSort = 'sort',
        private string $keywordFields = 'fields',
        private string $delimter = ',',
    ) {
    }

    public function skipForbiddenCriterias(bool $skip = true): self
    {
        $this->skipForbiddenCriterias = $skip;

        return $this;
    }

    public function parse(
        QueryBag $query,
        CriteriaCollection $allowdCriterias = new CriteriaCollection(),
    ): CriteriaCollection {
        return $this->parseFilters($query, $allowdCriterias)
            ->merge(
                $this->parseSorts($query, $allowdCriterias)
            )->merge(
                $this->parseFields($query, $allowdCriterias)
            );
    }

    private function parseFilters(QueryBag $query, CriteriaCollection $allowdCriterias): CriteriaCollection
    {
        $appliedCriterias = new CriteriaCollection();

        $queryStringFilters = $query->getArray($this->keywordFilter);

        foreach ($queryStringFilters as $filtername => $filterString) {
            if (! is_string($filtername)) {
                throw QueryStringException::unparseableQueryString();
            }

            if (! $allowdCriterias->hasFilter($filtername)) {
                if ($this->skipForbiddenCriterias) {
                    continue;
                }

                throw CriteriaException::forbiddenFilter($filtername, $allowdCriterias);
            }

            $values = $this->getValues($filterString);

            if (is_string($values) && $values === '') {
                continue;
            }

            if (is_array($values) && $values === []) {
                continue;
            }

            $filter = $allowdCriterias->getFilter($filtername);
            $filter->setValue($this->getValues($filterString));

            $appliedCriterias->add($filter);
        }

        return $appliedCriterias;
    }

    private function parseSorts(QueryBag $query, CriteriaCollection $allowdCriterias): CriteriaCollection
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

            if (! $allowdCriterias->hasSorting($value)) {
                if ($this->skipForbiddenCriterias) {
                    continue;
                }

                throw CriteriaException::forbiddenSorting($value, $allowdCriterias);
            }

            $sortCriteria = $allowdCriterias->getSorting($value);
            $sortCriteria->setSortDirection($sortDirection);

            $appliedCriterias->add($sortCriteria);
        }

        return $appliedCriterias;
    }

    private function parseFields(QueryBag $query, CriteriaCollection $allowdCriterias): CriteriaCollection
    {
        $appliedCriterias = new CriteriaCollection();

        $values = $this->getValues($query->getString($this->keywordFields));

        if (is_string($values)) {
            $values = [$values];
        }

        foreach ($values as $value) {
            if ($value === '') {
                continue;
            }

            if (! $allowdCriterias->hasAllowField($value)) {
                if ($this->skipForbiddenCriterias) {
                    continue;
                }

                throw CriteriaException::forbiddenSorting($value, $allowdCriterias);
            }

            $appliedCriterias->add($allowdCriterias->getAllowField($value));
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

        $trimmedValues = array_map(static function (string $value): string {
            return trim($value);
        }, explode($this->delimter, $filterString));

        return array_filter($trimmedValues, static function (string $value): bool {
            return $value !== '';
        });
    }
}
