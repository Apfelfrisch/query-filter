<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\QueryBuilder;
use Closure;

final class CallbackFilter implements Filter
{
    private string $field;

    /**
     * @template T of QueryBuilder
     * @param string|array<int, string>|null $value
     * @param Closure(T $builder, string $name, string|array<int, string>|null $value):mixed $callback
     **/
    public function __construct(
        private string $name,
        private Closure $callback,
        private string|array|null $value = null
    ) {
        $this->field = $this->name;
    }

    public function forField(string $field): self
    {
        $this->field = $field;

        return $this;
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
        ($this->callback)($builder, $this->field, $this->value);

        return $builder;
    }
}
