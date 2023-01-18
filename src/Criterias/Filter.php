<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Criterias;

interface Filter extends Criteria
{
    /** @param string|array<int, string> $value */
    public function setValue(string|array $value): void;
}
