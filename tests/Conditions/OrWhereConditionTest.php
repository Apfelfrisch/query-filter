<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Conditions;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\OrWhereCondition;
use Apfelfrisch\QueryFilter\Tests\TestCase;

final class OrWhereConditionTest extends TestCase
{
    /** @test */
    public function testOrWhereCondition()
    {
        $condition = new OrWhereCondition('field', Operator::Equal, 'value');

        $this->assertSame($condition->field, 'field');
        $this->assertSame($condition->operator, Operator::Equal);
        $this->assertSame($condition->value, 'value');
    }
}
