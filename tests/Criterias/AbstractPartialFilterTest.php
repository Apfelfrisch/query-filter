<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Criterias\AbstractPartialFilter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryBuilderAdapter;

abstract class AbstractPartialFilterTest extends TestCase
{
    abstract protected function prepareValue(string $value): string;

    abstract protected function buildFilter(...$values): AbstractPartialFilter;

    /** @test */
    public function test_constructor_types(): void
    {
        $filter = $this->buildFilter('test-filter');
        $this->assertSame('test-filter', $filter->getName());
    }

    public function test_setting_field_name(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = $this->buildFilter('test-field', 'test-value')->forField('field');
        $filter->apply($queryBuilder);

        $this->assertCount(1, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals('field', $queryBuilder->getCondition('whereConditions')[0]->field);
    }

    public function test_apply_value_string_to_query_builder(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = $this->buildFilter('test-field', 'test-value');

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getCondition('whereInConditions'));
        $this->assertEquals(
            new WhereCondition('test-field', Operator::Like, $this->prepareValue('test-value')),
            current($queryBuilder->getCondition('whereConditions'))
        );
    }

    public function test_apply_value_array_string_to_query_builder(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = $this->buildFilter('test-field', ['test-value', 'test-value-two']);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));

        $this->assertEquals(
            [
                new OrWhereCondition('test-field', Operator::Like, $this->prepareValue('test-value')),
                new OrWhereCondition('test-field', Operator::Like, $this->prepareValue('test-value-two')),
            ],
            $queryBuilder->getCondition('whereConditions')
        );
    }

    public function test_ignore_empty_string_values(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;
        $filter = $this->buildFilter('test-filter', null);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getAllConditions());

        $queryBuilder = new DummyQueryBuilderAdapter;
        $filter = new PartialFilter('test-filter', '');

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getAllConditions());

        $queryBuilder = new DummyQueryBuilderAdapter;
        $filter = new PartialFilter('test-filter', ['', '']);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(0, $queryBuilder->getAllConditions());
    }
}
