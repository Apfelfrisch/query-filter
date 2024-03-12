<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests;

use Apfelfrisch\QueryFilter\Adapters\DoctrineQueryBuilder;
use Apfelfrisch\QueryFilter\Adapters\EloquentQueryBuilder;
use Apfelfrisch\QueryFilter\Adapters\SimpleQueryParser;
use Apfelfrisch\QueryFilter\Criterias\ExactFilter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;
use Apfelfrisch\QueryFilter\Exceptions\QueryFilterException;
use Apfelfrisch\QueryFilter\Settings;
use Apfelfrisch\QueryFilter\Tests\Doubles\DummyQueryBuilderAdapter;
use Apfelfrisch\QueryFilter\Tests\Doubles\DummyQueryParser;
use Doctrine\DBAL\Query\QueryBuilder as DoctrineBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

final class SettingsTest extends TestCase
{
    public function test_load_default_setting(): void
    {
        $settings = new Settings;

        $this->assertInstanceOf(SimpleQueryParser::class, $settings->getQueryParser());
        $this->assertSame(PartialFilter::class, $settings->getDefaultFilterClass());
        $this->assertInstanceOf(EloquentQueryBuilder::class, $settings->adaptQueryBuilder($this->createStub(EloquentBuilder::class)));
        $this->assertInstanceOf(DoctrineQueryBuilder::class, $settings->adaptQueryBuilder($this->createStub(DoctrineBuilder::class)));
    }

    public function test_set_a_customer_query_parser(): void
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

    public function test_set_a_customer_filter(): void
    {
        $settings = new Settings;

        $settings->setDefaultFilterClass(ExactFilter::class);

        $this->assertSame(ExactFilter::class, $settings->getDefaultFilterClass());
    }

    public function test_throw_exception_if_filter_string_does_not_implement_filter_interface(): void
    {
        $settings = new Settings;

        $this->expectException(QueryFilterException::class);
        $this->expectExceptionMessage(
            '[Apfelfrisch\QueryFilter\Settings::setDefaultFilterClass] only exepts class strings of [Apfelfrisch\QueryFilter\Criterias\Filter]'
        );

        $settings->setDefaultFilterClass(self::class);
    }

    public function test_throw_exception_if_adaptable_is_unkown(): void
    {
        $settings = new Settings;

        $this->expectException(QueryFilterException::class);
        $this->expectExceptionMessage(
            'Unkown adaptable QueryBuilder class [Apfelfrisch\QueryFilter\Tests\UnkownClass]'
        );

        $settings->addQueryBuilderMapping(UnkownClass::class, DummyQueryBuilderAdapter::class);
    }

    public function test_throw_exception_if_adapter_missing_interface(): void
    {
        $settings = new Settings;

        $this->expectException(QueryFilterException::class);
        $this->expectExceptionMessage(
            'Adapter [Apfelfrisch\QueryFilter\Tests\Doubles\DummyQueryParser] must implement [Apfelfrisch\QueryFilter\QueryBuilder].'
        );

        $settings->addQueryBuilderMapping(EloquentBuilder::class, DummyQueryParser::class);
    }

    public function test_throw_exception_if_adapter_could_not_found(): void
    {
        $settings = new Settings;

        $this->expectException(QueryFilterException::class);
        $this->expectExceptionMessage(
            'Could not find Adapter for [Apfelfrisch\QueryFilter\Tests\Doubles\DummyQueryParser]'
        );

        $settings->adaptQueryBuilder(new DummyQueryParser);
    }
}
