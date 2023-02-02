<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests;

use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\Criterias\AllowField;
use Apfelfrisch\QueryFilter\Criterias\ExactFilter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;
use Apfelfrisch\QueryFilter\Criterias\Sorting;
use Apfelfrisch\QueryFilter\QueryBag;
use Apfelfrisch\QueryFilter\QueryFilter;
use Apfelfrisch\QueryFilter\QueryParser;
use Apfelfrisch\QueryFilter\Settings;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryBuilderAdapter;
use Apfelfrisch\QueryFilter\Tests\TestsDoubles\DummyQueryParser;

final class QueryFilterTest extends TestCase
{
    private QueryParser $uriParser;
    private Settings $settings;

    public function setUp(): void
    {
        $this->uriParser = new DummyQueryParser;
        $this->settings = (new Settings)->setQueryParser($this->uriParser);
    }

    /** @test */
    public function test_adding_allow_filters(): void
    {
        $allowFilterOne = new ExactFilter('test-excat-filter');
        $allowFilterTwo = new PartialFilter('test-partial-filter-two');

        $uriFilter = QueryFilter::new($this->settings);
        $uriFilter->allowFilters($allowFilterOne, $allowFilterTwo);

        $this->assertInstanceOf(CriteriaCollection::class, $uriFilter->getCriterias());
        $this->assertSame($allowFilterOne, $this->uriParser->allowedCriterias->getFilter('test-excat-filter'));
        $this->assertSame($allowFilterTwo, $this->uriParser->allowedCriterias->getFilter('test-partial-filter-two'));
    }

    /** @test */
    public function test_adding_default_criterias(): void
    {
        $filter = ExactFilter::new('test-excat-filter')->setValue('1');
        $sorting = new Sorting('test-sort-one');
        $criterias = QueryFilter::new()
            ->addDefaultCriterias($filter, $sorting)
            ->getCriterias([]);

        $this->assertSame($filter, $criterias->getFilter('test-excat-filter'));
        $this->assertSame($sorting, $criterias->getSorting('test-sort-one'));
    }

    /** @test */
    public function test_adding_default_filter_class(): void
    {
        $uriFilter = QueryFilter::new($this->settings);
        $uriFilter->allowFilters('default-filter', 'default-filter-two');

        $this->assertInstanceOf(CriteriaCollection::class, $uriFilter->getCriterias());
        $this->assertInstanceof($this->settings->getDefaultFilterClass(), $this->uriParser->allowedCriterias->getFilter('default-filter'));
        $this->assertInstanceof($this->settings->getDefaultFilterClass(), $this->uriParser->allowedCriterias->getFilter('default-filter-two'));

        $uriParser = new DummyQueryParser;
        $settings = (new Settings)->setDefaultFilterClass(ExactFilter::class)->setQueryParser($uriParser);
        $uriFilter = QueryFilter::new($settings);
        $uriFilter->allowFilters('default-filter', 'default-filter-two');

        $this->assertInstanceOf(CriteriaCollection::class, $uriFilter->getCriterias());
        $this->assertInstanceof(ExactFilter::class, $uriParser->allowedCriterias->getFilter('default-filter'));
        $this->assertInstanceof(ExactFilter::class, $uriParser->allowedCriterias->getFilter('default-filter-two'));
    }

    /** @test */
    public function test_adding_allow_sort(): void
    {
        $allowSortOne = new Sorting('test-sort-one');
        $allowSortTwo = new Sorting('test-sort-two');

        $uriFilter = QueryFilter::new($this->settings);
        $uriFilter->allowSorts($allowSortOne, $allowSortTwo);

        $this->assertInstanceOf(CriteriaCollection::class, $uriFilter->getCriterias());
        $this->assertSame($allowSortOne, $this->uriParser->allowedCriterias->getSorting('test-sort-one'));
        $this->assertSame($allowSortTwo, $this->uriParser->allowedCriterias->getSorting('test-sort-two'));
    }

    /** @test */
    public function test_adding_allow_fields(): void
    {
        $allowFieldOne = AllowField::new('test-field-one');
        $allowFieldTwo = AllowField::new('test-field-two')->as('alias');

        $uriFilter = QueryFilter::new($this->settings);
        $uriFilter->allowFields($allowFieldOne, $allowFieldTwo);

        $this->assertInstanceOf(CriteriaCollection::class, $uriFilter->getCriterias());
        $this->assertSame($allowFieldOne, $this->uriParser->allowedCriterias->getAllowField('test-field-one'));
        $this->assertSame($allowFieldTwo, $this->uriParser->allowedCriterias->getAllowField('test-field-two-as-alias'));
    }

    /** @test */
    public function test_parse_with_mappend_query_adapter(): void
    {
        $uriParser = new DummyQueryParser;
        $settings = (new Settings)->setQueryParser($uriParser);
        $adaptableClass = new class {};

        $settings->addQueryBuilderMapping($adaptableClass::class, DummyQueryBuilderAdapter::class);

        $uriFilter = QueryFilter::new($settings);

        $this->assertSame($adaptableClass, $uriFilter->applyOn($adaptableClass));
    }

    /** @test */
    public function test_parse_query_bag_parameters(): void
    {
        $uriFilter = QueryFilter::new($this->settings);
        $uriFilter->getCriterias(new QueryBag(['test-string' => 'test']));

        $this->assertEquals(new QueryBag(['test-string' => 'test']), $this->uriParser->query);
    }

    /** @test */
    public function test_parse_plain_array_query_parameters(): void
    {
        $uriFilter = QueryFilter::new($this->settings);
        $uriFilter->getCriterias(['test-string' => 'test']);

        $this->assertEquals(new QueryBag(['test-string' => 'test']), $this->uriParser->query);
    }

    /** @test */
    public function test_parse_global_get_query_parameters(): void
    {
        $_GET['test-string'] = 'test';

        $uriFilter = QueryFilter::new($this->settings);
        $uriFilter->getCriterias(null);

        $this->assertEquals(new QueryBag(['test-string' => 'test']), $this->uriParser->query);
    }

    /** @test */
    public function test_skipping_forbidden_criterias(): void
    {
        $uriParser = new DummyQueryParser;

        $settings = (new Settings)
            ->setQueryParser($uriParser)
            ->setSkipForbiddenCriterias(true);

        $this->assertFalse($uriParser->skipForbiddenCriterias);

        // Skipping Option from Settings
        $uriFilter = QueryFilter::new($settings);
        $uriFilter->getCriterias(null);
        $this->assertTrue($uriParser->skipForbiddenCriterias);

        // Skipping Option explicit false
        $uriFilter->skipForbiddenCriterias(false);
        $uriFilter->getCriterias(null);

        $this->assertFalse($uriParser->skipForbiddenCriterias);

        // Skipping Option explicit true
        $uriFilter->skipForbiddenCriterias(true);
        $uriFilter->getCriterias(null);

        $this->assertTrue($uriParser->skipForbiddenCriterias);
    }

    /** @test */
    public function test_cloning_instance(): void
    {
        $emptyFiler = new QueryFilter;
        $defaultCriteria = clone $emptyFiler;
        $allowdCriteria = clone $emptyFiler;
        $allowdSorts = clone $emptyFiler;

        $defaultCriteria->addDefaultCriterias(ExactFilter::new('criteria'));
        $allowdCriteria->allowFilters(ExactFilter::new('criteria'));
        $allowdSorts->allowSorts(new Sorting('criteria'));

        $this->assertFalse($this->getPrivateProberty($emptyFiler, 'defaultCriterias')->hasFilter('criteria'));
        $this->assertFalse($this->getPrivateProberty($emptyFiler, 'allowdCriterias')->hasFilter('criteria'));
        $this->assertFalse($this->getPrivateProberty($emptyFiler, 'allowdCriterias')->hasSorting('criteria'));

        $this->assertTrue($this->getPrivateProberty($defaultCriteria, 'defaultCriterias')->hasFilter('criteria'));
        $this->assertFalse($this->getPrivateProberty($defaultCriteria, 'allowdCriterias')->hasFilter('criteria'));
        $this->assertFalse($this->getPrivateProberty($defaultCriteria, 'allowdCriterias')->hasSorting('criteria'));

        $this->assertFalse($this->getPrivateProberty($allowdCriteria, 'defaultCriterias')->hasFilter('criteria'));
        $this->assertTrue($this->getPrivateProberty($allowdCriteria, 'allowdCriterias')->hasFilter('criteria'));
        $this->assertFalse($this->getPrivateProberty($allowdCriteria, 'allowdCriterias')->hasSorting('criteria'));

        $this->assertFalse($this->getPrivateProberty($allowdSorts, 'defaultCriterias')->hasFilter('criteria'));
        $this->assertFalse($this->getPrivateProberty($allowdSorts, 'allowdCriterias')->hasFilter('criteria'));
        $this->assertTrue($this->getPrivateProberty($allowdSorts, 'allowdCriterias')->hasSorting('criteria'));
    }

    private function getPrivateProberty(QueryFilter $queryFilter, string $probertyName): CriteriaCollection
    {
        $reflectionProperty = new \ReflectionProperty(QueryFilter::class, $probertyName);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($queryFilter);
    }
}
