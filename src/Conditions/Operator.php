<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Conditions;

enum Operator: string
{
    case Equal = '=';
    case GreaterThen = '>';
    case GreaterThenEqual = '>=';
    case LessThan = '<';
    case LessThanEqual = '<=';
    case Like = 'like';
}
