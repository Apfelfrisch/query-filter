<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\QueryBuilder;
use Closure;

final class CallbackFilter implements Filter
{
    private string $column;

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
        $this->column = $this->name;
    }

    /**
     * @template T of QueryBuilder
     * @param string|array<int, string>|null $value
     * @param Closure(T $builder, string $name, string|array<int, string>|null $value):mixed $callback
     **/
    public static function new(string $name, Closure $callback, string|array|null $value = null): self
    {
        return new self($name, $callback, $value);
    }

    public function forColumn(string $column): self
    {
        $this->column = $column;

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
        ($this->callback)($builder, $this->column, $this->value);

        return $builder;
    }
}
