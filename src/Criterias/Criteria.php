<?php

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\QueryBuilder;

interface Criteria
{
    public function getName(): string;

    /**
     * @template T
     * @param QueryBuilder<T> $builder
     * @return QueryBuilder<T>
     */
    public function apply(QueryBuilder $builder): QueryBuilder;
}
