<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\QueryBuilder;

final class AllowField implements Criteria
{
    private string|null $alias = null;

    public function __construct(
        private string $name,
    ) {
    }

    public static function new(string $name): self
    {
        return new self($name);
    }

    public function as(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getName(): string
    {
        return $this->alias === null ? $this->name : "$this->name-as-$this->alias";
    }

    public function apply(QueryBuilder $builder): QueryBuilder
    {
        return $builder->select(
            $this->alias === null ? $this->name : "$this->name as $this->alias"
        );
    }
}
