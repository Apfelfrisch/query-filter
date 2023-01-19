<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Adapters;

use Apfelfrisch\QueryFilter\Adapters\SimpleQueryParser;
use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\Criterias\ExactFilter;
use Apfelfrisch\QueryFilter\Criterias\PartialFilter;
use Apfelfrisch\QueryFilter\Criterias\Sorting;
use Apfelfrisch\QueryFilter\Exceptions\CriteriaException;
use Apfelfrisch\QueryFilter\Exceptions\QueryStringException;
use Apfelfrisch\QueryFilter\QueryBag;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Exception;

final class SimpleQueryParserTest extends TestCase
{
    /** @test */
    public function test_throwing_exception_when_given_filter_is_not_allowd(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("filter[street]=Dukelweg"));

        $this->expectException(CriteriaException::class);
        $this->expectExceptionMessage('Requested filter [street] is not allowed. Allowed filter(s) are [name , age]');

        $parser->parse(new CriteriaCollection(new PartialFilter('name'), new PartialFilter('age')));
    }

    /** @test */
    public function test_throwing_exception_when_given_sort_is_not_allowd(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("sort=name"));

        $this->expectException(CriteriaException::class);
        $this->expectExceptionMessage('Requested sorting [name] is not allowed. Allowed sort(s) are [street , street_no]');

        $parser->parse(allowedSorts: new CriteriaCollection(new Sorting('street'), new Sorting('street_no')));
    }

    /** @test */
    public function test_throwing_exception_when_given_a_nested_filter_type(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("filter[[name]]=nils"));

        $this->expectException(Exception::class);

        $parser->parse(new CriteriaCollection);
    }

    /** @test */
    public function test_throwing_exception_when_given_a_nested_sort(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("sort[name]=nils"));

        $this->expectException(Exception::class);

        $parser->parse(new CriteriaCollection);
    }

    /** @test */
    public function test_throwing_exception_query_is_unparseable(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(new QueryBag(['filter' => ['name' => ['nils']]]));

        $this->expectException(QueryStringException::class);
        $this->expectExceptionMessage('Could not parse query string');

        $parser->parse(new CriteriaCollection(new PartialFilter('name')));
    }

    /** @test */
    public function test_allowd_filter_with_one_value(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("filter[name]=nils"));

        $crierias = $parser->parse(new CriteriaCollection(new ExactFilter('name')));

        $this->assertEquals(
            new CriteriaCollection(new ExactFilter('name', 'nils')),
            $crierias
        );
    }

    /** @test */
    public function test_allowd_filter_with_multiple_values(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("filter[name]=nils,refle"));

        $crierias = $parser->parse(new CriteriaCollection(new ExactFilter('name')));

        $this->assertEquals(
            new CriteriaCollection(new ExactFilter('name', ['nils', 'refle'])),
            $crierias
        );
    }

    /** @test */
    public function test_allowd_sorting_with_one_ascinding_value(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("sort=nils"));

        $crierias = $parser->parse(new CriteriaCollection, new CriteriaCollection(new Sorting('nils')));

        $this->assertEquals(
            new CriteriaCollection(new Sorting('nils', SortDirection::Ascending)),
            $crierias
        );
    }

    /** @test */
    public function test_allowd_sorting_with_one_descing_value(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("sort=-nils"));

        $crierias = $parser->parse(new CriteriaCollection, new CriteriaCollection(new Sorting('nils')));

        $this->assertEquals(
            new CriteriaCollection(new Sorting('nils', SortDirection::Descending)),
            $crierias
        );
    }

    /** @test */
    public function test_allowd_sorting_with_multiple_values(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("sort=-nils,refle"));

        $crierias = $parser->parse(new CriteriaCollection, new CriteriaCollection(
            new Sorting('nils'),
            new Sorting('refle'),
        ));

        $this->assertEquals(
            new CriteriaCollection(
                new Sorting('nils', SortDirection::Descending),
                new Sorting('refle', SortDirection::Ascending),
            ),
            $crierias
        );
    }

    /** @test */
    public function test_trimming_spaces_in_sort_value(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("sort= -nils"));

        $crierias = $parser->parse(new CriteriaCollection, new CriteriaCollection(new Sorting('nils')));

        $this->assertEquals(
            new CriteriaCollection(new Sorting('nils', SortDirection::Descending)),
            $crierias
        );
    }

    /** @test */
    public function test_skipping_empty_sorts(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("sort= ,nils"));

        $crierias = $parser->parse(new CriteriaCollection, new CriteriaCollection(new Sorting('nils')));

        $this->assertEquals(
            new CriteriaCollection(new Sorting('nils', SortDirection::Ascending)),
            $crierias
        );
    }

    /** @test */
    public function test_skipping_empty_filter(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("filter[name]= &filter[name_two]= nils"));

        $crierias = $parser->parse(new CriteriaCollection(
            new ExactFilter('name'),
            new ExactFilter('name_two'),
        ));

        $this->assertEquals(
            new CriteriaCollection(new ExactFilter('name_two', 'nils')),
            $crierias
        );
    }

    /** @test */
    public function test_trimming_spaces_in_sort_lists(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("sort=-nils, refle"));

        $crierias = $parser->parse(new CriteriaCollection, new CriteriaCollection(
            new Sorting('nils'),
            new Sorting('refle'),
        ));

        $this->assertEquals(
            new CriteriaCollection(
                new Sorting('nils', SortDirection::Descending),
                new Sorting('refle', SortDirection::Ascending),
            ),
            $crierias
        );
    }

    /** @test */
    public function test_trimming_spaces_in_filter_lists(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->setQuery(QueryBag::fromUrl("filter[name]=nils, refle"));

        $crierias = $parser->parse(new CriteriaCollection(new ExactFilter('name')));

        $this->assertEquals(
            new CriteriaCollection(new ExactFilter('name', ['nils', 'refle'])),
            $crierias
        );
    }
}
