<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Criterias\BetweenFilter;
use Apfelfrisch\QueryFilter\Tests\Doubles\DummyQueryBuilderAdapter;
use Apfelfrisch\QueryFilter\Tests\TestCase;

final class BetweenFilterTest extends TestCase
{
    /** @test */
    public function test_constructor_types(): void
    {
        $filter = new BetweenFilter('test-filter');
        $this->assertSame('test-filter', $filter->getName());
    }

    public function test_throw_exception_when_values_are_ivalid(): void
    {
        $filter = new BetweenFilter('test-filter');

        $this->expectExceptionMessage('Value for Apfelfrisch\QueryFilter\Criterias\BetweenFilter has to be an array with two strings');
        $filter->setValue('test');

        $this->expectExceptionMessage('Value for Apfelfrisch\QueryFilter\Criterias\BetweenFilter has to be an array with two strings');
        $filter->setValue(['test']);

        $this->expectExceptionMessage('Value for Apfelfrisch\QueryFilter\Criterias\BetweenFilter has to be an array with two strings');
        $filter->setValue([1, 2]);
    }

    public function test_apply_value_string_to_query_builder(): void
    {
        $from = '2020-01-01';
        $until = '2022-12-31';

        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new BetweenFilter('test-filter');
        $filter->setValue([$from, $until]);

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(2, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals(
            new WhereCondition('test-filter', Operator::GreaterThenEqual, '2020-01-01'),
            $queryBuilder->getCondition('whereConditions')[0]
        );
        $this->assertEquals(
            new WhereCondition('test-filter', Operator::LessThanEqual, '2022-12-31'),
            $queryBuilder->getCondition('whereConditions')[1]
        );
    }

    public function test_ignore_when_conditions_when_from_or_until_is_null(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = new BetweenFilter('test-filter', '1');
        $filter->apply($queryBuilder);

        $this->assertCount(0, $queryBuilder->getCondition('whereConditions'));

        $filter = new BetweenFilter('test-filter', null, '1');
        $filter->apply($queryBuilder);

        $this->assertCount(0, $queryBuilder->getCondition('whereConditions'));
    }

    public function test_setting_column_name(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $filter = BetweenFilter::new('test-filter', '1', '2')->forColumn('column');
        $filter->apply($queryBuilder);

        $this->assertCount(2, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals('column', $queryBuilder->getCondition('whereConditions')[0]->column);
        $this->assertEquals('column', $queryBuilder->getCondition('whereConditions')[1]->column);
    }
}
