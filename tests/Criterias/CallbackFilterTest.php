<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Criterias\CallbackFilter;
use Apfelfrisch\QueryFilter\QueryBuilder;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryBuilderAdapter;

final class CallbackFilterTest extends TestCase
{
    /** @test */
    public function test_constructor_types(): void
    {
        $filter = new CallbackFilter('test-filter', fn (QueryBuilder $builder, string $name, string|array|null $value): mixed => '');
        $this->assertSame('test-filter', $filter->getName());
    }

    public function test_setting_field_name(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $closure = function (QueryBuilder $builder, string $name, string $value): void {
            $builder->where(new WhereCondition($name, Operator::LessThan, $value));
        };

        $filter = CallbackFilter::new('test-filter', $closure, 'value');
        $filter->forField('field');
        $filter->apply($queryBuilder);

        $this->assertCount(1, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals('field', $queryBuilder->getCondition('whereConditions')[0]->field);
    }

    public function test_excecuting_a_callback_filter_on_the_query_builder(): void
    {
        $queryBuilder = new DummyQueryBuilderAdapter;

        $closure = function (QueryBuilder $builder, string $name, string|array|null $value): void {
            $value = is_array($value) ?: [$value ?? ''];
            $builder->where(
                new WhereCondition($name, Operator::LessThan, current($value)),
                new OrWhereCondition($name, Operator::GreaterThen, current($value)),
            );

            $builder->whereIn(new WhereInCondition($name, $value));
        };

        $filter = new CallbackFilter('test-field', $closure, 'test-value');

        $this->assertSame($queryBuilder, $filter->apply($queryBuilder));
        $this->assertCount(2, $queryBuilder->getCondition('whereConditions'));
        $this->assertEquals(
            [
                new WhereCondition('test-field', Operator::LessThan, 'test-value'),
                new OrWhereCondition('test-field', Operator::GreaterThen, 'test-value'),
            ],
            $queryBuilder->getCondition('whereConditions')
        );

        $this->assertEquals(
            new WhereInCondition('test-field', ['test-value']),
            current($queryBuilder->getCondition('whereInConditions'))
        );
    }
}
