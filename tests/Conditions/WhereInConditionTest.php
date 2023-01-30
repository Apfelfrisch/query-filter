<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Conditions;

use Apfelfrisch\QueryFilter\Conditions\WhereInCondition;
use Apfelfrisch\QueryFilter\Exceptions\ConditionException;
use Apfelfrisch\QueryFilter\Tests\TestCase;

final class WhereInConditionTest extends TestCase
{
    /** @test */
    public function testThrowExceptionWhenValuesHasNullableValue(): void
    {
        $this->expectException(ConditionException::class);
        $this->expectExceptionMessage('Nullable values are not allowd for WhereInConditions.');

        $this->assertInstanceOf(WhereInCondition::class, new WhereInCondition('test', [null]));
    }
}
