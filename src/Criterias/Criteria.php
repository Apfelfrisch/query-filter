<?php

namespace Apfelfrisch\QueryFilter\Criterias;

use Apfelfrisch\QueryFilter\QueryBuilder;

interface Criteria
{
    public function getName(): string;

    public function getType(): Type;

    public function apply(QueryBuilder $builder): QueryBuilder;
}
