<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Criterias\ExactFilter;
use Apfelfrisch\QueryFilter\Tests\Doubles\DummyQueryBuilderAdapter;
use Apfelfrisch\QueryFilter\Tests\TestCase;

final class ExactFilterTest extends TestCase
{
    /** @test */
    public function test_constructor_types(): void
    {
        $filter = new ExactFilter('test-filter');
        $this->assertSame('test-filter', $filter->getName());
    }

    public function test_setting_column_name(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = ExactFilter::new('test-filter', 'test-value')->forColumn('column');
        $filter->apply($queryBuilder);

        $this->assertCount(1, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals('column', $queryBuilder->getCondition('whereConditions')[0]->column);
    }

    public function test_apply_value_string_to_query_builder(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new ExactFilter('test-column', 'test-value');

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getCondition('whereInConditions'));
        $this->assertEquals(
            new WhereCondition('test-column', Operator::Equal, 'test-value'),
            current($queryBuilder->getCondition('whereConditions'))
        );
    }

    public function test_apply_value_array_string_to_query_builder(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new ExactFilter('test-column', ['test-value', 'test-value-two']);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals(
            new WhereInCondition('test-column', ['test-value', 'test-value-two']),
            current($queryBuilder->getCondition('whereInConditions'))
        );
    }

    public function test_accept_empty_string_and_null_values(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;
        $filter = new ExactFilter('test-filter', null);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(1, $queryBuilder->getAllConditions());

        $filter = new ExactFilter('test-filter', '');
        $queryBuilder = new DummyQueryBuilderAdapter;

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(1, $queryBuilder->getAllConditions());

        $queryBuilder = new DummyQueryBuilderAdapter;
        $filter = new ExactFilter('test-filter', ['', null]);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(2, $queryBuilder->getAllConditions());
    }
}
