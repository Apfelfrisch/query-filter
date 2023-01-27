<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Criterias\Filter;
use Apfelfrisch\QueryFilter\QueryBuilder;
use Apfelfrisch\QueryFilter\Tests\TestCase;

final class CriteriaTest extends TestCase
{
    /** @test */
    public function test_criteria_interface(): void
    {
        $filter = $this->createStub(Filter::class);

        $this->assertIsString($filter->getName());
        $this->assertInstanceOf(QueryBuilder::class, $filter->apply($this->createStub(QueryBuilder::class)));
    }
}
