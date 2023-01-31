<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Adapters;

use Apfelfrisch\QueryFilter\Adapters\DoctrineQueryBuilder;
use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;

final class DoctrineQueryBuilderTest extends TestCase
{
    public function test_providing_the_giving_builder(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $this->assertSame($builder, $builderAdapter->builder());
    }

    /**
     * @test
     * @dataProvider provideWhereOperatorCases
     */
    public function test_single_where(Operator $operator): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-column', $operator, 'test-value')
        );

        $this->assertSame('SELECT * FROM users WHERE test-column ' . $operator->value . ' :test-column', (string)$builder);
        $this->assertEquals([':test-column' => 'test-value'], $builder->getParameters());
    }

    /** @test */
    public function test_single_where_null(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(new WhereCondition('test-column', Operator::Equal, null));

        $this->assertSame('SELECT * FROM users WHERE test-column IS NULL', (string)$builder);
        $this->assertEquals([], $builder->getParameters());

        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);
        $builderAdapter->where(new WhereCondition('test-column', Operator::NotEqual, null));

        $this->assertSame('SELECT * FROM users WHERE test-column IS NOT NULL', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_multiple_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-column', Operator::Equal, 'test-value'),
            new WhereCondition('test-column-two', Operator::LessThanEqual, 'test-value-two')
        );

        $this->assertSame('SELECT * FROM users WHERE (test-column = :test-column) AND (test-column-two <= :test-column-two)', (string)$builder);
        $this->assertEquals([':test-column' => 'test-value', ':test-column-two' => 'test-value-two'], $builder->getParameters());
    }

    /** @test */
    public function test_two_or_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-column', Operator::Equal, 'test-value'),
            new OrWhereCondition('test-column-two', Operator::Equal, 'test-value-two'),
        );

        $this->assertSame('SELECT * FROM users WHERE (test-column = :test-column) OR (test-column-two = :test-column-two)', (string)$builder);
        $this->assertEquals([':test-column' => 'test-value', ':test-column-two' => 'test-value-two'], $builder->getParameters());
    }

    /** @test */
    public function test_mix_where_and_or_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-column-1', Operator::Equal, 'test-value-1'),
            new WhereCondition('test-column-2', Operator::Equal, 'test-value-2'),
        );

        $builderAdapter->where(
            new WhereCondition('test-column-3', Operator::Equal, 'test-value-3'),
            new OrWhereCondition('test-column-4', Operator::Equal, 'test-value-4'),
        );

        $this->assertSame(
            'SELECT * FROM users WHERE ((test-column-1 = :test-column-1) AND (test-column-2 = :test-column-2)) AND ((test-column-3 = :test-column-3) OR (test-column-4 = :test-column-4))',
            (string)$builder
        );
        $this->assertEquals(
            [':test-column-1' => 'test-value-1', ':test-column-2' => 'test-value-2', ':test-column-3' => 'test-value-3', ':test-column-4' => 'test-value-4'],
            $builder->getParameters()
        );
    }

    /** @test */
    public function test_where_in(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->whereIn(
            new WhereInCondition('test-column', ['test-value', 'test-value-two', 'test-value-three']),
        );

        $this->assertSame('SELECT * FROM users WHERE test-column IN (:test-column)', (string)$builder);
        $this->assertEquals([':test-column' => ['test-value', 'test-value-two', 'test-value-three']], $builder->getParameters());
    }

    /** @test */
    public function test_selects(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->select('name', 'DATE(brithday)');
        $builderAdapter->select('city');

        $this->assertSame('SELECT name, DATE(brithday), city FROM users', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column asc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_multiple_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Ascending);
        $builderAdapter->sort('test-column-two', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column asc, test-column-two asc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column desc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_multiple_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);
        $builderAdapter->sort('test-column-two', SortDirection::Descending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column desc, test-column-two desc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_multiple_mixed_order(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);
        $builderAdapter->sort('test-column-two', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column desc, test-column-two asc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    private function getBuilder(): QueryBuilder
    {
        /** @var MockObject|Connection */
        $connection = $this->createMock(Connection::class);

        $expressionBuilder = new ExpressionBuilder($connection);

        $connection->expects(self::any())
           ->method('getExpressionBuilder')
           ->willReturn($expressionBuilder);

        $builder = new QueryBuilder($connection);
        $builder->select('*')->from('users');

        return $builder;
    }

    public function provideWhereOperatorCases(): iterable
    {
        yield 'operator-eq' => [Operator::Equal];
        yield 'operator-gt' => [Operator::GreaterThen];
        yield 'operator-gte' => [Operator::GreaterThenEqual];
        yield 'operator-lt' => [Operator::LessThan];
        yield 'operator-lte' => [Operator::LessThanEqual];
    }
}
