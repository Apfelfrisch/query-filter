<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter;

interface QueryParser
{
    public function skipForbiddenCriterias(bool $skip = true): self;

    public function forceCamelCase(bool $forceCamelCase = true): self;

    public function parse(QueryBag $query, CriteriaCollection $allowdCriterias): CriteriaCollection;
}
