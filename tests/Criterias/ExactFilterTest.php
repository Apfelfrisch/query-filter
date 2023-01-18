<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Criterias\ExactFilter;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryBuilderAdapter;

final class ExactFilterTest extends TestCase
{
    /** @test */
    public function test_constructor_types(): void
    {
        $filter = new ExactFilter('test-filter');
        $this->assertSame('test-filter', $filter->getName());
    }

    public function test_setting_field_name(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = ExactFilter::new('test-filter', 'test-value')->forField('field');
        $filter->apply($queryBuilder);

        $this->assertCount(1, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals('field', $queryBuilder->getCondition('whereConditions')[0]->field);
    }

    public function test_apply_value_string_to_query_builder(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new ExactFilter('test-field', 'test-value');

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getCondition('whereInConditions'));
        $this->assertEquals(
            new WhereCondition('test-field', Operator::Equal, 'test-value'),
            current($queryBuilder->getCondition('whereConditions'))
        );
    }

    public function test_apply_value_array_string_to_query_builder(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new ExactFilter('test-field', ['test-value', 'test-value-two']);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals(
            new WhereInCondition('test-field', ['test-value', 'test-value-two']),
            current($queryBuilder->getCondition('whereInConditions'))
        );
    }

    public function test_ignore_empty_string_values(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;
        $filter = new ExactFilter('test-filter', null);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getAllConditions());

        $filter = new ExactFilter('test-filter', '');
        $queryBuilder = new DummyQueryBuilderAdapter;

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getAllConditions());

        $queryBuilder = new DummyQueryBuilderAdapter;
        $filter = new ExactFilter('test-filter', ['', '']);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getAllConditions());
    }
}
