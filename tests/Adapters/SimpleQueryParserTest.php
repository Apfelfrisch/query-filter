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

        $this->expectException(CriteriaException::class);
        $this->expectExceptionMessage('Requested filter [street] is not allowed. Allowed filter(s) are [name , age]');

        $parser->parse(
            QueryBag::fromUrl("filter[street]=Dukelweg"),
            new CriteriaCollection(new PartialFilter('name'), new PartialFilter('age'))
        );
    }

    /** @test */
    public function test_throwing_exception_when_given_sort_is_not_allowd(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $this->expectException(CriteriaException::class);
        $this->expectExceptionMessage('Requested sorting [name] is not allowed. Allowed sort(s) are [street , street_no]');

        $parser->parse(
            QueryBag::fromUrl("sort=name"),
            new CriteriaCollection(new Sorting('street'), new Sorting('street_no'))
        );
    }

    /** @test */
    public function test_skipping_forbidding_filters(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->skipForbiddenCriterias();

        $criterias = $parser->parse(
            QueryBag::fromUrl("filter[street]=nils&filter[name]=nils"),
            new CriteriaCollection(new ExactFilter('name'))
        );

        $this->assertEquals(new CriteriaCollection(new ExactFilter('name', 'nils')), $criterias);
    }

    /** @test */
    public function test_skipping_forbidding_sorts(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');
        $parser->skipForbiddenCriterias();

        $criterias = $parser->parse(
            QueryBag::fromUrl("sort=street,name,street_no"),
            new CriteriaCollection(new Sorting('street'), new Sorting('street_no'))
        );

        $this->assertEquals(
            new CriteriaCollection(new Sorting('street'), new Sorting('street_no')),
            $criterias
        );
    }

    /** @test */
    public function test_throwing_exception_when_given_a_nested_filter_type(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $this->expectException(Exception::class);

        $parser->parse(QueryBag::fromUrl("filter[[name]]=nils"), new CriteriaCollection);
    }

    /** @test */
    public function test_throwing_exception_when_given_a_nested_sort(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $this->expectException(Exception::class);

        $parser->parse(QueryBag::fromUrl("sort[name]=nils"), new CriteriaCollection);
    }

    /** @test */
    public function test_throwing_exception_query_is_unparseable(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $this->expectException(QueryStringException::class);
        $this->expectExceptionMessage('Could not parse query string');

        $parser->parse(
            new QueryBag(['filter' => ['name' => ['nils']]]),
            new CriteriaCollection(new PartialFilter('name'))
        );
    }

    /** @test */
    public function test_allowd_filter_with_one_value(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("filter[name]=nils"),
            new CriteriaCollection(new ExactFilter('name'))
        );

        $this->assertEquals(new CriteriaCollection(new ExactFilter('name', 'nils')), $criterias);
    }

    /** @test */
    public function test_allowd_filter_with_multiple_values(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("filter[name]=nils,refle"),
            new CriteriaCollection(new ExactFilter('name'))
        );

        $this->assertEquals(new CriteriaCollection(new ExactFilter('name', ['nils', 'refle'])), $criterias);
    }

    /** @test */
    public function test_allowd_sorting_with_one_ascinding_value(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("sort=nils"),
            new CriteriaCollection(new Sorting('nils'))
        );

        $this->assertEquals(new CriteriaCollection(new Sorting('nils', SortDirection::Ascending)), $criterias);
    }

    /** @test */
    public function test_allowd_sorting_with_one_descing_value(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("sort=-nils"),
            new CriteriaCollection(new Sorting('nils'))
        );

        $this->assertEquals(new CriteriaCollection(new Sorting('nils', SortDirection::Descending)), $criterias);
    }

    /** @test */
    public function test_allowd_sorting_with_multiple_values(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("sort=-nils,refle"),
            new CriteriaCollection(new Sorting('nils'), new Sorting('refle'))
        );

        $this->assertEquals(
            new CriteriaCollection(
                new Sorting('nils', SortDirection::Descending),
                new Sorting('refle', SortDirection::Ascending),
            ),
            $criterias
        );
    }

    /** @test */
    public function test_trimming_spaces_in_sort_value(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("sort= -nils"),
            new CriteriaCollection(new Sorting('nils'))
        );

        $this->assertEquals(new CriteriaCollection(new Sorting('nils', SortDirection::Descending)), $criterias);
    }

    /** @test */
    public function test_skipping_empty_sorts(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("sort= ,nils"),
            new CriteriaCollection(new Sorting('nils'))
        );

        $this->assertEquals(new CriteriaCollection(new Sorting('nils', SortDirection::Ascending)), $criterias);
    }

    /** @test */
    public function test_skipping_empty_filter(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("filter[name]= &filter[name_two]= , &filter[name_three]= nils"),
            new CriteriaCollection(new ExactFilter('name'), new ExactFilter('name_two'), new ExactFilter('name_three'))
        );

        $this->assertEquals(new CriteriaCollection(new ExactFilter('name_three', 'nils')), $criterias);
    }

    /** @test */
    public function test_trimming_spaces_in_sort_lists(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("sort=-nils, refle"),
            new CriteriaCollection(new Sorting('nils'), new Sorting('refle'))
        );

        $this->assertEquals(
            new CriteriaCollection(
                new Sorting('nils', SortDirection::Descending),
                new Sorting('refle', SortDirection::Ascending),
            ),
            $criterias
        );
    }

    /** @test */
    public function test_trimming_spaces_in_filter_lists(): void
    {
        $parser = new SimpleQueryParser(keywordFilter: 'filter', keywordSort: 'sort');

        $criterias = $parser->parse(
            QueryBag::fromUrl("filter[name]=nils, refle"),
            new CriteriaCollection(new ExactFilter('name'))
        );

        $this->assertEquals(new CriteriaCollection(new ExactFilter('name', ['nils', 'refle'])), $criterias);
    }
}
