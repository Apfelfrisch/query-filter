<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Criterias;

use Apfelfrisch\QueryFilter\Criterias;
use Apfelfrisch\QueryFilter\CriteriaCollection;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Exception;

final class CriteriaCollectionTest extends TestCase
{
    /** @test */
    public function test_add_criteria_to_collection(): void
    {
        $creteriaCollection = new CriteriaCollection;

        $creteriaCollection->add(new Criterias\ExactFilter('test-name', 'test-value'));
        $creteriaCollection->add(new Criterias\ExactFilter('test-name-two', 'test-value'));

        $this->assertTrue($creteriaCollection->has('test-name'));
        $this->assertTrue($creteriaCollection->has('test-name-two'));
        $this->assertFalse($creteriaCollection->has('test-name-three'));
    }

    /** @test */
    public function test_provide_a_criterie_by_name(): void
    {
        $criteria = new Criterias\ExactFilter('test-name', 'test-value');

        $creteriaCollection = new CriteriaCollection;
        $creteriaCollection->add($criteria);

        $this->assertSame($criteria, $creteriaCollection->get($criteria->getName()));
    }

    /**
     * @test
     * @dataProvider provideCriteriaTypes
     */
    public function test_criteria_collection_has_criteria_of_type(Criterias\Criteria $criteria, bool $hasFilter, bool $hasSorting): void
    {
        $creteriaCollection = new CriteriaCollection;

        $creteriaCollection->add($criteria);

        $this->assertSame($hasFilter, $creteriaCollection->hasFilter('test-name'));
        $this->assertSame($hasSorting, $creteriaCollection->hasSorting('test-name'));
    }

    /**
     * @test
     * @dataProvider provideCriteriaTypes
     */
    public function test_criteria_collection_provides_criteria_of_type(Criterias\Criteria $criteria, bool $isFilter, bool $isSorting): void
    {
        $creteriaCollection = new CriteriaCollection;

        $creteriaCollection->add($criteria);

        if ($isSorting) {
            $this->expectException(Exception::class);
        }
        $this->assertInstanceOf(Criterias\Criteria::class, $creteriaCollection->getFilter('test-name'));

        if ($isFilter) {
            $this->expectException(Exception::class);
        }
        $this->assertInstanceOf(Criterias\Criteria::class, $creteriaCollection->getSorting('test-name'));
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
