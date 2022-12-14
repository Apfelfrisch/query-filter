<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests;

use Apfelfrisch\QueryFilter\Adapters\DoctrineQueryBuilder;
use Apfelfrisch\QueryFilter\Adapters\EloquentQueryBuilder;
use Apfelfrisch\QueryFilter\Adapters\SimpleQueryParser;
use Apfelfrisch\QueryFilter\Settings;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryBuilderAdapter;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryParser;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineBuilder;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

final class SettingsTest extends TestCase
{
    /** @test */
    public function test_load_default_setting(): void
    {
        $settings = new Settings;

        $this->assertInstanceOf(SimpleQueryParser::class, $settings->getQueryParser());
        $this->assertInstanceOf(EloquentQueryBuilder::class, $settings->adaptQueryBuilder($this->createStub(EloquentBuilder::class)));
        $this->assertInstanceOf(DoctrineQueryBuilder::class, $settings->adaptQueryBuilder($this->createStub(DoctrineBuilder::class)));
    }

    public function test_setting_a_customer_query_parser(): void
    {
        $settings = new Settings;

        $customerQueryParser = new DummyQueryParser;

        $settings->setQueryParser($customerQueryParser);

        $this->assertSame($customerQueryParser, $settings->getQueryParser());
    }

    public function test_adding_a_customer_query_adapter(): void
    {
        $settings = new Settings;

        $settings->addQueryBuilderMapping(EloquentBuilder::class, DummyQueryBuilderAdapter::class);

        $this->assertInstanceOf(DummyQueryBuilderAdapter::class, $settings->adaptQueryBuilder($this->createStub(EloquentBuilder::class)));
    }

    public function test_throw_exception_if_adaptable_is_unkown(): void
    {
        $settings = new Settings;

        $this->expectException(Exception::class);

        $settings->addQueryBuilderMapping(UnkownClass::class, DummyQueryBuilderAdapter::class);
    }

    public function test_throw_exception_if_adapter_missing_interface(): void
    {
        $settings = new Settings;

        $this->expectException(Exception::class);

        $settings->addQueryBuilderMapping(EloquentBuilder::class, DummyQueryParser::class);
    }

    public function test_throw_exception_if_adapter_could_not_found(): void
    {
        $settings = new Settings;

        $this->expectException(Exception::class);

        $settings->adaptQueryBuilder(new DummyQueryParser);
    }
}
