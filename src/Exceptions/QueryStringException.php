<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Exceptions;

class QueryStringException extends QueryFilterException
{
    public static function unparseableQueryString(): self
    {
        return new self("Could not parse query string");
    }
}
