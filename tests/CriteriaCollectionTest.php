<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests;

use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\Criterias;
use Apfelfrisch\QueryFilter\Exceptions\CriteriaException;
use Exception;

final class CriteriaCollectionTest extends TestCase
{
    /** @test */
    public function test_add_filter_to_collection(): void
    {
        $criteriaCollection = new CriteriaCollection;

        $criteriaCollection->add(new Criterias\ExactFilter('test-name', 'test-value'));
        $criteriaCollection->add(new Criterias\ExactFilter('test-name-two', 'test-value'));

        $this->assertTrue($criteriaCollection->hasFilter('test-name'));
        $this->assertTrue($criteriaCollection->hasFilter('test-name-two'));
        $this->assertFalse($criteriaCollection->hasFilter('test-name-three'));
    }

    /** @test */
    public function test_provide_a_filter_by_name(): void
    {
        $criteria = new Criterias\ExactFilter('test-name', 'test-value');

        $criteriaCollection = new CriteriaCollection;
        $criteriaCollection->add($criteria);

        $this->assertSame($criteria, $criteriaCollection->getFilter($criteria->getName()));
    }

    /** @test */
    public function test_throw_exception_when_allow_field_was_not_found(): void
    {
        $criteriaCollection = new CriteriaCollection;

        $this->expectException(CriteriaException::class);
        $this->expectExceptionMessage('AllowField [test-name] is not found');

        $criteriaCollection->getAllowField('test-name');
    }

    /** @test */
    public function test_throw_exception_when_filter_was_not_found(): void
    {
        $criteriaCollection = new CriteriaCollection;

        $this->expectException(CriteriaException::class);
        $this->expectExceptionMessage('Filter [test-name] is not found');

        $criteriaCollection->getFilter('test-name');
    }

    /** @test */
    public function test_throw_exception_when_sorting_was_not_found(): void
    {
        $criteriaCollection = new CriteriaCollection;

        $this->expectException(CriteriaException::class);
        $this->expectExceptionMessage('Sorting [test-name] is not found');

        $criteriaCollection->getSorting('test-name');
    }

    /**
     * @test
     * @dataProvider provideCriteriaTypes
     */
    public function test_criteria_collection_has_criteria_of_type(
        CriteriaCollection $criteriaCollection,
        bool $hasFilter,
        bool $hasSorting,
        bool $hasAllowField,
    ): void {
        $this->assertSame($hasFilter, $criteriaCollection->hasFilter('test-name'));
        $this->assertSame($hasSorting, $criteriaCollection->hasSorting('test-name'));
        $this->assertSame($hasAllowField, $criteriaCollection->hasAllowField('test-name'));
    }

    /**
     * @test
     * @dataProvider provideCriteriaTypes
     */
    public function test_criteria_collection_provides_criteria_of_type(
        CriteriaCollection $criteriaCollection,
        bool $hasFilter,
        bool $hasSorting,
        bool $hasAllowField,
    ): void {
        if (! $hasSorting || ! $hasAllowField) {
            $this->expectException(Exception::class);
        }
        $this->assertInstanceOf(Criterias\Criteria::class, $criteriaCollection->getFilter('test-name'));

        if (! $hasFilter || ! $hasAllowField) {
            $this->expectException(Exception::class);
        }
        $this->assertInstanceOf(Criterias\Criteria::class, $criteriaCollection->getSorting('test-name'));

        if (! $hasSorting || ! $hasAllowField) {
            $this->expectException(Exception::class);
        }
        $this->assertInstanceOf(Criterias\Criteria::class, $criteriaCollection->getAllowField('test-name'));
    }

    public function test_criteria_collection_provides_a_filtered_criteria_collection(): void
    {
        $criteriaCollection = new CriteriaCollection;

        $criteriaCollection->add(new Criterias\AllowField('allow-name'));
        $criteriaCollection->add(new Criterias\ExactFilter('test-name', 'test-value'));
        $criteriaCollection->add(new Criterias\PartialFilter('test-name-two', 'test-value-two'));
        $criteriaCollection->add(new Criterias\Sorting('sorting'));
        $criteriaCollection->add(new Criterias\Sorting('sorting-two'));
        $criteriaCollection->add(new Criterias\Sorting('sorting-three'));

        $this->assertCount(1, $criteriaCollection->onlyAllowFields());
        $this->assertCount(2, $criteriaCollection->onlyFilters());
        $this->assertCount(3, $criteriaCollection->onlySorts());
        $this->assertCount(6, $criteriaCollection);
    }

    public function test_merge_criteria_collections(): void
    {
        $criteriaCollectionOne = new CriteriaCollection(
            new Criterias\ExactFilter('test-name', 'test-value')
        );

        $criteriaCollectionTwo = new CriteriaCollection(
            new Criterias\Sorting('sorting')
        );

        $criteriaCollectionThree = new CriteriaCollection(
            new Criterias\AllowField('allow-field')
        );

        $mergedCriterias = $criteriaCollectionOne->merge($criteriaCollectionTwo, $criteriaCollectionThree);

        $this->assertCount(3, $mergedCriterias);
        $this->assertTrue($mergedCriterias->hasFilter('test-name'));
        $this->assertTrue($mergedCriterias->hasSorting('sorting'));
        $this->assertTrue($mergedCriterias->hasAllowField('allow-field'));
    }

    public function provideCriteriaTypes(): iterable
    {
        yield 'pure-filters' => [
            new CriteriaCollection(new Criterias\ExactFilter('test-name', 'test-value')), true, false, false,
        ];

        yield 'pure-sorts' => [
            new CriteriaCollection(new Criterias\Sorting('test-name')), false, true, false,
        ];

        yield 'pure-allow-fields' => [
            new CriteriaCollection(new Criterias\AllowField('test-name')), false, false, true,
        ];
    }
}
