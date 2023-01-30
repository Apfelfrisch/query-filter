<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Exceptions;

use Apfelfrisch\QueryFilter\Conditions\Operator;

class ConditionException extends QueryFilterException
{
    public static function invalidOperatorForNullableField(Operator $operator): self
    {
        return new self("Invalid operator [" . $operator->value . "] on nullable value.");
    }
}
