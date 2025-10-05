<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Adapters;

use Apfelfrisch\QueryFilter\Adapters\DoctrineOrmQueryBuilder;
use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Tests\Doubles\UserEntity;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\DataProvider;

final class DoctrineOrmQueryBuilderTest extends TestCase
{
    public function test_providing_the_giving_builder(): void
    {
        $builder = $this->getBuilder();

        $builderAdapter = new DoctrineOrmQueryBuilder($builder, 'u');

        $this->assertSame($builder, $builderAdapter->builder());
    }

    #[DataProvider('provideWhereOperatorCases')]
    public function test_single_where(Operator $operator): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineOrmQueryBuilder($builder, 'u');

        $builderAdapter->where(
            new WhereCondition('testColumn', $operator, 'test-value'),
        );

        $this->assertSame('SELECT u FROM ' . UserEntity::class . ' u WHERE u.testColumn ' . $operator->value . ' :testColumn', (string) $builder);
    }

    public function test_single_where_null(): void
    {
        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineOrmQueryBuilder($builder, 'u');

        $builderAdapter->where(new WhereCondition('testColumn', Operator::Equal, null));

        $this->assertSame('SELECT u FROM ' . UserEntity::class . ' u WHERE u.testColumn IS NULL', (string) $builder);

        $builder = $this->getBuilder();
        $builderAdapter = new DoctrineOrmQueryBuilder($builder, 'u');
        $builderAdapter->where(new WhereCondition('testColumn', Operator::NotEqual, null));

        $this->assertSame('SELECT u FROM ' . UserEntity::class . ' u WHERE u.testColumn IS NOT NULL', (string) $builder);
    }

    private function getBuilder(): QueryBuilder
    {
        $config = new Configuration();
        $driver = new AttributeDriver([__DIR__ . '../Doubles']);
        $config->setMetadataDriverImpl($driver);
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Proxy');
        $config->setAutoGenerateProxyClasses(true);

        $conn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $entityManager = new EntityManager($conn, $config);

        $qb = $entityManager->createQueryBuilder();

        $qb->select('u')->from(UserEntity::class, 'u');

        return $qb;
    }

    public static function provideWhereOperatorCases(): iterable
    {
        yield 'operator-eq' => [Operator::Equal];
        yield 'operator-gt' => [Operator::GreaterThen];
        yield 'operator-gte' => [Operator::GreaterThenEqual];
        yield 'operator-lt' => [Operator::LessThan];
        yield 'operator-lte' => [Operator::LessThanEqual];
    }
}
