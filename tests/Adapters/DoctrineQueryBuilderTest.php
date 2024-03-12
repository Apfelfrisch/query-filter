<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Adapters;

use Apfelfrisch\QueryFilter\Adapters\DoctrineQueryBuilder;
use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Criterias\BetweenFilter;
use Apfelfrisch\QueryFilter\QueryFilter;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\SchemaConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

final class DoctrineQueryBuilderTest extends TestCase
{
    public function test_providing_the_giving_builder(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $this->assertSame($builder, $builderAdapter->builder());
    }

    #[DataProvider('provideWhereOperatorCases')]
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

    public function test_selects(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->select('name', 'DATE(brithday)');
        $builderAdapter->select('city');

        $this->assertSame('SELECT name, DATE(brithday), city FROM users', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    public function test_sort_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column asc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    public function test_sort_multiple_asc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Ascending);
        $builderAdapter->sort('test-column-two', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column asc, test-column-two asc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    public function test_sort_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column desc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    public function test_sort_multiple_desc(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);
        $builderAdapter->sort('test-column-two', SortDirection::Descending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column desc, test-column-two desc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
    }

    public function test_sort_multiple_mixed_order(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineQueryBuilder($builder);

        $builderAdapter->sort('test-column', SortDirection::Descending);
        $builderAdapter->sort('test-column-two', SortDirection::Ascending);

        $this->assertSame('SELECT * FROM users ORDER BY test-column desc, test-column-two asc', (string)$builder);
        $this->assertEquals([], $builder->getParameters());
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
            'SELECT name, email FROM users WHERE ((created_at >= :created_at) AND (created_at <= :created_at)) AND (name LIKE :name) ORDER BY street desc',
            (string)$builder
        );
    }

    public static function provideWhereOperatorCases(): iterable
    {
        yield 'operator-eq' => [Operator::Equal];
        yield 'operator-gt' => [Operator::GreaterThen];
        yield 'operator-gte' => [Operator::GreaterThenEqual];
        yield 'operator-lt' => [Operator::LessThan];
        yield 'operator-lte' => [Operator::LessThanEqual];
    }

    private function getBuilder(): QueryBuilder
    {
        /** @var MockObject|Connection */
        $connection = $this->getMockBuilder(Connection::class)
            ->setConstructorArgs([[], $this->createDriverMock()])
            ->onlyMethods(['quote'])
            ->getMockForAbstractClass();
        $connection->method('quote')->willReturnCallback(static fn (string $input) => sprintf("'%s'", $input));

        // $expressionBuilder = new ExpressionBuilder($connection);

        // $connection->expects(self::any())
        //    ->method('getExpressionBuilder')
        //    ->willReturn($expressionBuilder);

        $builder = new QueryBuilder($connection);
        $builder->select('*')->from('users');

        return $builder;
    }

    private function createDriverMock(): Driver
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')
            ->willReturn(false);

        $connection = $this->createMock(Driver\Connection::class);
        $connection->method('query')
            ->willReturn($result);

        $driver = $this->createMock(Driver::class);
        $driver->method('connect')
            ->willReturn($connection);
        $driver->method('getDatabasePlatform')
            ->willReturn($platform = $this->createPlatformMock());

        if (method_exists(Driver::class, 'getSchemaManager')) {
            $driver->method('getSchemaManager')
                ->willReturnCallback([$platform, 'createSchemaManager']);
        }

        return $driver;
    }

    private function createPlatformMock(): AbstractPlatform
    {
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->method('createSchemaConfig')
            ->willReturn(new SchemaConfig());

        $platform = $this->getMockBuilder(AbstractPlatform::class)
            ->onlyMethods(['supportsIdentityColumns', 'createSchemaManager'])
            ->getMockForAbstractClass();
        $platform->method('supportsIdentityColumns')
            ->willReturn(true);
        $platform->method('createSchemaManager')
            ->willReturn($schemaManager);

        return $platform;
    }
}
