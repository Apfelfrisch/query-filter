<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\QueryBuilder;
use Closure;

final class CallbackFilter implements Filter
{
    /**
     * @param string|array<int, string>|null $value
     * @param Closure(QueryBuilder $builder, string $name, string|array<int, string>|null $value):mixed $callback
     **/
    public function __construct(
        private string $name,
        private Closure $callback,
        private string|array|null $value = null
    ) {
    }

    /** @param string|array<int, string> $value */
    public function setValue(string|array $value): void
    {
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        ($this->callback)($builder, $this->name, $this->value);

        return $builder;
    }
}
