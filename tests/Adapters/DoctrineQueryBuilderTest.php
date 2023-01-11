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
    /**
     * @test
     * @dataProvider provideWhereOperatorCases
     */
    public function test_single_where(Operator $operator): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-field', $operator, 'test-value')
        );

        $this->assertSame('SELECT * FROM users WHERE test-field ' . $operator->value . ' :test-field', (string)$builder);
        $this->assertEquals([':test-field' => 'test-value'], $builder->getParameters());
    }

    /** @test */
    public function test_multiple_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-field', Operator::Equal, 'test-value'),
            new WhereCondition('test-field-two', Operator::LessThanEqual, 'test-value-two')
        );

        $this->assertSame('SELECT * FROM users WHERE (test-field = :test-field) AND (test-field-two <= :test-field-two)', (string)$builder);
        $this->assertEquals([':test-field' => 'test-value', ':test-field-two' => 'test-value-two'], $builder->getParameters());
    }

    /** @test */
    public function test_two_or_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-field', Operator::Equal, 'test-value'),
            new OrWhereCondition('test-field-two', Operator::Equal, 'test-value-two'),
        );

        $this->assertSame('SELECT * FROM users WHERE (test-field = :test-field) OR (test-field-two = :test-field-two)', (string)$builder);
        $this->assertEquals([':test-field' => 'test-value', ':test-field-two' => 'test-value-two'], $builder->getParameters());
    }

    /** @test */
    public function test_mix_where_and_or_where(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->where(
            new WhereCondition('test-field-1', Operator::Equal, 'test-value-1'),
            new WhereCondition('test-field-2', Operator::Equal, 'test-value-2'),
        );

        $builderAdapter->where(
            new WhereCondition('test-field-3', Operator::Equal, 'test-value-3'),
            new OrWhereCondition('test-field-4', Operator::Equal, 'test-value-4'),
        );

        $this->assertSame(
            'SELECT * FROM users WHERE ((test-field-1 = :test-field-1) AND (test-field-2 = :test-field-2)) AND ((test-field-3 = :test-field-3) OR (test-field-4 = :test-field-4))',
            (string)$builder
        );
        $this->assertEquals(
            [':test-field-1' => 'test-value-1', ':test-field-2' => 'test-value-2', ':test-field-3' => 'test-value-3', ':test-field-4' => 'test-value-4'],
            $builder->getParameters()
        );
    }

    /** @test */
    public function test_where_in(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->whereIn(
            new WhereInCondition('test-field', ['test-value', 'test-value-two', 'test-value-three']),
        );

        $this->assertSame('SELECT * FROM users WHERE test-field IN (:test-field)', (string)$builder);
        $this->assertEquals([':test-field' => ['test-value', 'test-value-two', 'test-value-three']], $builder->getParameters());
    }

    /** @test */
    public function test_sort_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-field asc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_multiple_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Ascending);
        $builderAdapter->sort('test-field-two', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-field asc, test-field-two asc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Descending);

        $this->assertSame('SELECT * FROM users ORDER BY test-field desc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_multiple_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Descending);
        $builderAdapter->sort('test-field-two', SortDirection::Descending);

        $this->assertSame('SELECT * FROM users ORDER BY test-field desc, test-field-two desc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    /** @test */
    public function test_sort_multiple_mixed_order(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-field', SortDirection::Descending);
        $builderAdapter->sort('test-field-two', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-field desc, test-field-two asc', (string)$builder);
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
