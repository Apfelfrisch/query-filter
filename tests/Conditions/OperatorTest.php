<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Conditions;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Tests\TestCase;

final class OperatorTest extends TestCase
{
    /** @test */
    public function testOperatorEnumValues()
    {
        $this->assertSame(Operator::Equal->value, '=');
        $this->assertSame(Operator::GreaterThen->value, '>');
        $this->assertSame(Operator::GreaterThenEqual->value, '>=');
        $this->assertSame(Operator::LessThan->value, '<');
        $this->assertSame(Operator::LessThanEqual->value, '<=');
        $this->assertSame(Operator::Like->value, 'like');
    }
}
