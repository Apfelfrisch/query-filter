<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Conditions;

use Apfelfrisch\QueryFilter\Conditions\SortCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Tests\TestCase;

final class SortConditionTest extends TestCase
{
    /** @test */
    public function testSortCondition()
    {
        $condition = new SortCondition('name', SortDirection::Ascending);
        $this->assertSame($condition->column, 'name');
        $this->assertSame($condition->sortDirection, SortDirection::Ascending);
    }
}
