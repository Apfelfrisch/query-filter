<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Criterias\FixedFilter;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryBuilderAdapter;

final class FixedFilterTest extends TestCase
{
    /** @test */
    public function test_constructor_types(): void
    {
        $filter = new FixedFilter('test-filter', new WhereCondition('column', Operator::Equal, 'value'));
        $this->assertSame('test-filter', $filter->getName());
    }

    public function test_apply_fiven_conditions(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new FixedFilter(
            'test-column',
            new WhereCondition('column', Operator::Equal, 'value'),
            new OrWhereCondition('column-two', Operator::LessThan, 'value-two'),
            new OrWhereCondition('column-three', Operator::LessThan, 'value-three'),
        );

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(3, $queryBuilder->getCondition('whereConditions'));
    }
}
