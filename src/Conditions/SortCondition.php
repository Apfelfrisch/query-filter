<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Conditions;

final class SortCondition
{
    public function __construct(
        public readonly string $name,
        public readonly SortDirection $sortDirection,
    ) {
    }
}
