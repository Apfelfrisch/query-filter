<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Adapters;

use Apfelfrisch\QueryFilter\Adapters\EloquentQueryBuilder;
use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

final class EloquentQueryBuilderTest extends TestCase
{
    /** @test */
    public function test_single_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(new WhereCondition('test-field', Operator::Equals, 'test-value'));

        $this->assertSame('select * where ("test-field" = ?)', $builder->toSql());
        $this->assertEquals([0 => 'test-value'], $builder->getBindings());
    }

    /** @test */
    public function test_multiple_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-field', Operator::Equals, 'test-value'),
            new WhereCondition('test-field-two', Operator::Equals, 'test-value-two')
        );

        $this->assertSame('select * where ("test-field" = ? and "test-field-two" = ?)', $builder->toSql());
        $this->assertEquals([0 => 'test-value', 1 => 'test-value-two'], $builder->getBindings());
    }

    /** @test */
    public function test_two_or_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-field', Operator::Equals, 'test-value'),
            new OrWhereCondition('test-field-two', Operator::Equals, 'test-value-two'),
        );

        $this->assertSame('select * where ("test-field" = ? or "test-field-two" = ?)', $builder->toSql());
        $this->assertEquals([0 => 'test-value', 1 => 'test-value-two'], $builder->getBindings());
    }

    /** @test */
    public function test_mix_where_and_or_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-field-1', Operator::Equals, 'test-value-1'),
            new WhereCondition('test-field-2', Operator::Equals, 'test-value-2'),
        );

        $builderAdapter->where(
            new WhereCondition('test-field-3', Operator::Equals, 'test-value-3'),
            new OrWhereCondition('test-field-4', Operator::Equals, 'test-value-4'),
        );

        $this->assertSame(
            'select * where ("test-field-1" = ? and "test-field-2" = ?) and ("test-field-3" = ? or "test-field-4" = ?)',
            $builder->toSql()
        );
        $this->assertEquals(
            [0 => 'test-value-1', 1 => 'test-value-2', 2 => 'test-value-3', 3 => 'test-value-4'],
            $builder->getBindings()
        );
    }

    /** @test */
    public function test_where_in(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->whereIn(
            new WhereInCondition('test-field', ['test-value', 'test-value-two', 'test-value-three']),
        );

        $this->assertSame('select * where "test-field" in (?, ?, ?)', $builder->toSql());
        $this->assertEquals([0 => 'test-value', 1 => 'test-value-two', 2 => 'test-value-three'], $builder->getBindings());
    }

    /** @test */
    public function test_sort_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Ascending);

        $this->assertSame('select * order by "test-field" asc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    /** @test */
    public function test_sort_multiple_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Ascending);
        $builderAdapter->sort('test-field-two', SortDirection::Ascending);

        $this->assertSame('select * order by "test-field" asc, "test-field-two" asc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    /** @test */
    public function test_sort_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Descending);

        $this->assertSame('select * order by "test-field" desc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    /** @test */
    public function test_sort_multiple_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Descending);
        $builderAdapter->sort('test-field-two', SortDirection::Descending);

        $this->assertSame('select * order by "test-field" desc, "test-field-two" desc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    /** @test */
    public function test_sort_multiple_mixed_order(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Descending);
        $builderAdapter->sort('test-field-two', SortDirection::Ascending);

        $this->assertSame('select * order by "test-field" desc, "test-field-two" asc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    private function getBuilder(): Builder
    {
        return new Builder(
            $this->createStub(ConnectionInterface::class),
            new Grammar,
            $this->createStub(Processor::class)
        );
    }
}
