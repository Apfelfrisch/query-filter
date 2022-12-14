<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Conditions;

enum SortDirection: string
{
    case Ascending = 'asc';
    case Descinding = 'desc';
}
