<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Conditions;

enum Operator: string
{
    case Equals = '=';
    case GreaterThen = '>';
    case GreaterThenEquals = '>=';
    case LessThan = '<';
    case LessThanEquals = '<=';
    case Like = 'like';
}
