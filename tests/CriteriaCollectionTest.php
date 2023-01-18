<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests;

use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\Criterias;
use Apfelfrisch\QueryFilter\Criterias\Sorting;
use Exception;

final class CriteriaCollectionTest extends TestCase
{
    /** @test */
    public function test_add_criteria_to_collection(): void
    {
        $criteriaCollection = new CriteriaCollection;

        $criteriaCollection->add(new Criterias\ExactFilter('test-name', 'test-value'));
        $criteriaCollection->add(new Criterias\ExactFilter('test-name-two', 'test-value'));

        $this->assertTrue($criteriaCollection->has('test-name'));
        $this->assertTrue($criteriaCollection->has('test-name-two'));
        $this->assertFalse($criteriaCollection->has('test-name-three'));
    }

    /** @test */
    public function test_provide_a_criterie_by_name(): void
    {
        $criteria = new Criterias\ExactFilter('test-name', 'test-value');

        $criteriaCollection = new CriteriaCollection;
        $criteriaCollection->add($criteria);

        $this->assertSame($criteria, $criteriaCollection->get($criteria->getName()));
    }

    /** @test */
    public function test_throw_exception_when_criteria_was_not_found(): void
    {
        $criteriaCollection = new CriteriaCollection;
        $criteriaCollection->add(new Criterias\ExactFilter('test-name', 'test-value'));

        $this->expectExceptionMessage('Criteria with name [test-name-two] not found');

        $criteriaCollection->get('test-name-two');
    }

    /** @test */
    public function test_throw_exception_when_filter_was_not_found(): void
    {
        $criteriaCollection = new CriteriaCollection;
        $criteriaCollection->add(new Sorting('test-name'));

        $this->expectExceptionMessage('Filter with name [test-name] not found');

        $criteriaCollection->getFilter('test-name');
    }

    /** @test */
    public function test_throw_exception_when_sorting_was_not_found(): void
    {
        $criteriaCollection = new CriteriaCollection;
        $criteriaCollection->add(new Criterias\ExactFilter('test-name', 'test-value'));

        $this->expectExceptionMessage('Sorting with name [test-name] not found');

        $criteriaCollection->getSorting('test-name');
    }

    /**
     * @test
     * @dataProvider provideCriteriaTypes
     */
    public function test_criteria_collection_has_criteria_of_type(Criterias\Criteria $criteria, bool $hasFilter, bool $hasSorting): void
    {
        $criteriaCollection = new CriteriaCollection;

        $criteriaCollection->add($criteria);

        $this->assertSame($hasFilter, $criteriaCollection->hasFilter('test-name'));
        $this->assertSame($hasSorting, $criteriaCollection->hasSorting('test-name'));
    }

    /**
     * @test
     * @dataProvider provideCriteriaTypes
     */
    public function test_criteria_collection_provides_criteria_of_type(Criterias\Criteria $criteria, bool $isFilter, bool $isSorting): void
    {
        $criteriaCollection = new CriteriaCollection;

        $criteriaCollection->add($criteria);

        if ($isSorting) {
            $this->expectException(Exception::class);
        }
        $this->assertInstanceOf(Criterias\Criteria::class, $criteriaCollection->getFilter('test-name'));

        if ($isFilter) {
            $this->expectException(Exception::class);
        }
        $this->assertInstanceOf(Criterias\Criteria::class, $criteriaCollection->getSorting('test-name'));
    }

    public function test_criteria_collection_provides_a_filtered_criteria_collection(): void
    {
        $criteriaCollection = new CriteriaCollection;

        $criteriaCollection->add(new Criterias\ExactFilter('test-name', 'test-value'));
        $criteriaCollection->add(new Criterias\PartialFilter('test-name-two', 'test-value-two'));
        $criteriaCollection->add(new Criterias\Sorting('sorting'));
        $criteriaCollection->add(new Criterias\Sorting('sorting-two'));
        $criteriaCollection->add(new Criterias\Sorting('sorting-three'));

        $this->assertCount(2, $criteriaCollection->onlyFilters());
        $this->assertCount(3, $criteriaCollection->onlySorts());
    }

    public function provideCriteriaTypes()
    {
        yield 'excat-filter' => [
            new Criterias\ExactFilter('test-name', 'test-value'), true, false,
        ];

        yield 'partial-filter' => [
            new Criterias\PartialFilter('test-name', 'test-value'), true, false,
        ];

        yield 'sorting' => [
            new Criterias\Sorting('test-name'), false, true,
        ];
    }
}
