<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Adapters;

use Apfelfrisch\QueryFilter\Adapters\EloquentQueryBuilder;
use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Criterias\BetweenFilter;
use Apfelfrisch\QueryFilter\QueryFilter;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

final class EloquentQueryBuilderTest extends TestCase
{
    public function test_providing_the_giving_builder(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $this->assertSame($builder, $builderAdapter->builder());
    }

    public function test_single_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(new WhereCondition('test-column', Operator::Equal, 'test-value'));

        $this->assertSame('select * from users where (test-column = ?)', $builder->toSql());
        $this->assertEquals([0 => 'test-value'], $builder->getBindings());
    }

    public function test_single_where_null(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(new WhereCondition('test-column', Operator::Equal, null));

        $this->assertSame('select * from users where (test-column is null)', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());

        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);
        $builderAdapter->where(new WhereCondition('test-column', Operator::NotEqual, null));

        $this->assertSame('select * from users where (test-column is not null)', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function test_multiple_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-column', Operator::Equal, 'test-value'),
            new WhereCondition('test-column-two', Operator::Equal, 'test-value-two')
        );

        $this->assertSame('select * from users where (test-column = ? and test-column-two = ?)', $builder->toSql());
        $this->assertEquals([0 => 'test-value', 1 => 'test-value-two'], $builder->getBindings());
    }

    public function test_two_or_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-column', Operator::Equal, 'test-value'),
            new OrWhereCondition('test-column-two', Operator::Equal, 'test-value-two'),
        );

        $this->assertSame('select * from users where (test-column = ? or test-column-two = ?)', $builder->toSql());
        $this->assertEquals([0 => 'test-value', 1 => 'test-value-two'], $builder->getBindings());
    }

    public function test_mix_where_and_or_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-column-1', Operator::Equal, 'test-value-1'),
            new WhereCondition('test-column-2', Operator::Equal, 'test-value-2'),
        );

        $builderAdapter->where(
            new WhereCondition('test-column-3', Operator::Equal, 'test-value-3'),
            new OrWhereCondition('test-column-4', Operator::Equal, 'test-value-4'),
        );

        $this->assertSame(
            'select * from users where (test-column-1 = ? and test-column-2 = ?) and (test-column-3 = ? or test-column-4 = ?)',
            $builder->toSql()
        );
        $this->assertEquals(
            [0 => 'test-value-1', 1 => 'test-value-2', 2 => 'test-value-3', 3 => 'test-value-4'],
            $builder->getBindings()
        );
    }

    public function test_where_in(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->whereIn(
            new WhereInCondition('test-column', ['test-value', 'test-value-two', 'test-value-three']),
        );

        $this->assertSame('select * from users where test-column in (?, ?, ?)', $builder->toSql());
        $this->assertEquals([0 => 'test-value', 1 => 'test-value-two', 2 => 'test-value-three'], $builder->getBindings());
    }

    public function test_selects(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->select('name', 'DATE(brithday)');
        $builderAdapter->select('city');

        $this->assertSame('select name, DATE(brithday), city from users', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function test_sort_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Ascending);

        $this->assertSame('select * from users order by test-column asc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function test_sort_multiple_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Ascending);
        $builderAdapter->sort('test-column-two', SortDirection::Ascending);

        $this->assertSame('select * from users order by test-column asc, test-column-two asc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function test_sort_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);

        $this->assertSame('select * from users order by test-column desc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function test_sort_multiple_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);
        $builderAdapter->sort('test-column-two', SortDirection::Descending);

        $this->assertSame('select * from users order by test-column desc, test-column-two desc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function test_sort_multiple_mixed_order(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new EloquentQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);
        $builderAdapter->sort('test-column-two', SortDirection::Ascending);

        $this->assertSame('select * from users order by test-column desc, test-column-two asc', $builder->toSql());
        $this->assertEquals([], $builder->getBindings());
    }

    public function test_adapter_on_query_filter(): void
    {
        $builder = QueryFilter::new()
            ->addDefaultCriterias(BetweenFilter::new('created_at', '2020-01-01', '2020-01-31'))
            ->allowFilters('name')
            ->allowSorts('street')
            ->allowFields('name', 'email')
            ->applyOn($this->getBuilder(), ['filter' => ['name' => 'nils'], 'sort' => '-street', 'fields' => 'name,email']);

        $this->assertSame(
            'select name, email from users where (created_at >= ? and created_at <= ?) and (name like ?) order by street desc',
            $builder->toSql()
        );
    }

    private function getBuilder(): Builder
    {
        $builder = new Builder(
            $this->createStub(ConnectionInterface::class),
            new Grammar,
            $this->createStub(Processor::class)
        );

        $builder->select('*')->fromRaw('users');

        return $builder;
    }
}
