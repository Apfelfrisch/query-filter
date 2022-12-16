<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Criterias\Sort;
use Apfelfrisch\QueryFilter\Criterias\Type;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryBuilderAdapter;

final class SortTest extends TestCase
{
    /** @test */
    public function test_constructor_types(): void
    {
        $filter = new Sort('test-filter');
        $this->assertSame('test-filter', $filter->getName());
        $this->assertSame(Type::Sort, $filter->getType());
        $this->assertSame(SortDirection::Ascending, $filter->getSortDirection());
    }

    public function test_apply_ascing_sort_on_query_filter(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new Sort('test-sort', SortDirection::Ascending);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(1, $queryBuilder->getCondition('sortConditions'));
        $this->assertEquals('test-sort', current(array_keys($queryBuilder->getCondition('sortConditions'))));
        $this->assertEquals(SortDirection::Ascending, current($queryBuilder->getCondition('sortConditions')));
    }

    public function test_apply_ascing_descing_on_query_filter(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new Sort('test-sort', SortDirection::Descending);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(1, $queryBuilder->getCondition('sortConditions'));
        $this->assertEquals('test-sort', current(array_keys($queryBuilder->getCondition('sortConditions'))));
        $this->assertEquals(SortDirection::Descending, current($queryBuilder->getCondition('sortConditions')));
    }
}
