<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests;

use Apfelfrisch\QueryFilter\Criterias\CriteriaCollection;
use Apfelfrisch\QueryFilter\Criterias\ExactFilter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;
use Apfelfrisch\QueryFilter\Criterias\Sort;
use Apfelfrisch\QueryFilter\QueryFilter;
use Apfelfrisch\QueryFilter\Settings;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryBuilderAdapter;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryParser;

final class QueryFilterTest extends TestCase
{
    /** @test */
    public function test_adding_allow_filters(): void
    {
        $queryString = 'test-string';
        $allowFilterOne = new ExactFilter('test-excat-filter');
        $allowFilterTwo = new PartialFilter('test-partial-filter-two');
        $uriParser = new DummyQueryParser;
        $settings = (new Settings)->setQueryParser($uriParser);

        $uriFilter = QueryFilter::new($settings);
        $uriFilter->allowFilters($allowFilterOne, $allowFilterTwo);

        $this->assertInstanceOf(CriteriaCollection::class, $uriFilter->getCriterias($queryString));
        $this->assertSame($queryString, $uriParser->queryStrig);
        $this->assertSame($allowFilterOne, $uriParser->allowedFilters->get('test-excat-filter'));
        $this->assertSame($allowFilterTwo, $uriParser->allowedFilters->get('test-partial-filter-two'));
    }

    /** @test */
    public function test_adding_allow_sort(): void
    {
        $queryString = 'test-string';
        $allowSortOne = new Sort('test-sort-one');
        $allowSortTwo = new Sort('test-sort-two');
        $uriParser = new DummyQueryParser;
        $settings = (new Settings)->setQueryParser($uriParser);

        $uriFilter = QueryFilter::new($settings);
        $uriFilter->allowSorts($allowSortOne, $allowSortTwo);

        $this->assertInstanceOf(CriteriaCollection::class, $uriFilter->getCriterias($queryString));
        $this->assertSame($queryString, $uriParser->queryStrig);
        $this->assertSame($allowSortOne, $uriParser->allowedSorts->get('test-sort-one'));
        $this->assertSame($allowSortTwo, $uriParser->allowedSorts->get('test-sort-two'));
    }

    /** @test */
    public function test_parse_with_mappend_query_adapter(): void
    {
        $uriParser = new DummyQueryParser;
        $settings = (new Settings)->setQueryParser($uriParser);
        $adaptableClass = new class {};

        $settings->addQueryBuilderMapping($adaptableClass::class, DummyQueryBuilderAdapter::class);

        $uriFilter = QueryFilter::new($settings);

        $this->assertSame($adaptableClass, $uriFilter->parse('test', $adaptableClass));
    }
}
