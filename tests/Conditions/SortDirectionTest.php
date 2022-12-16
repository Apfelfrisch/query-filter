<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Conditions;

use Apfelfrisch\QueryFilter\Conditions\SortDirection;
use Apfelfrisch\QueryFilter\Tests\TestCase;

final class SortDirectionTest extends TestCase
{
    /** @test */
    public function testSortDirectionEnumValues()
    {
        $this->assertSame(SortDirection::Ascending->value, 'asc');
        $this->assertSame(SortDirection::Descending->value, 'desc');
    }
}
