<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Exceptions;

use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\Criterias\Criteria;

class CriteriaException extends QueryFilterException
{
    public static function missingCriteria(string $name): self
    {
        return new self("Criteria [$name] is not found.");
    }

    public static function missingSorting(string $name): self
    {
        return new self("Sorting [$name] is not found.");
    }

    public static function missingFilter(string $name): self
    {
        return new self("Filter [$name] is not found.");
    }

    public static function forbiddenSorting(string $name, CriteriaCollection $allowedSorts): self
    {
        $message = "Requested sorting [$name] is not allowed. Allowed sort(s) are [" .  self::allowedCriteriaString($allowedSorts->onlySorts()) . "]";

        return new self($message);
    }

    public static function forbiddenFilter(string $name, CriteriaCollection $allowedFilters): self
    {
        $message = "Requested filter [$name] is not allowed. Allowed filter(s) are [" .  self::allowedCriteriaString($allowedFilters->onlyFilters()) . "]";

        return new self($message);
    }

    private static function allowedCriteriaString(CriteriaCollection $criterias): string
    {
        return implode(
            ' , ',
            array_map(fn (Criteria $criteria): string => $criteria->getName(), iterator_to_array($criterias))
        );
    }
}
