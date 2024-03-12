<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests\Conditions;

use Apfelfrisch\QueryFilter\Conditions\Operator;
use Apfelfrisch\QueryFilter\Conditions\WhereCondition;
use Apfelfrisch\QueryFilter\Exceptions\ConditionException;
use Apfelfrisch\QueryFilter\Tests\TestCase;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;

final class WhereConditionTest extends TestCase
{
    #[DataProvider('conditionAttributes')]
    public function testThrowExceptionWhenNullableValueHasInvalOperator($column, $operator, $value, $exceptionMessage): void
    {
        if ($exceptionMessage !== null) {
            $this->expectException(ConditionException::class);
            $this->expectExceptionMessage($exceptionMessage);
        }

        $this->assertInstanceOf(WhereCondition::class, new WhereCondition($column, $operator, $value));
    }

    public static function conditionAttributes(): Iterator
    {
        yield Operator::Equal->value => [
            'column', Operator::Equal, null, null,
        ];
        yield Operator::NotEqual->value => [
            'column', Operator::NotEqual, null, null,
        ];
        yield Operator::GreaterThen->value => [
            'column', Operator::GreaterThen, null, 'Invalid operator [' . Operator::GreaterThen->value . '] on nullable value.',
        ];
        yield Operator::GreaterThenEqual->value => [
            'column', Operator::GreaterThenEqual, null, 'Invalid operator [' . Operator::GreaterThenEqual->value . '] on nullable value.',
        ];
        yield Operator::LessThan->value => [
            'column', Operator::LessThan, null, 'Invalid operator [' . Operator::LessThan->value . '] on nullable value.',
        ];
        yield Operator::LessThanEqual->value => [
            'column', Operator::LessThanEqual, null, 'Invalid operator [' . Operator::LessThanEqual->value . '] on nullable value.',
        ];
        yield Operator::Like->value => [
            'column', Operator::Like, null, 'Invalid operator [' . Operator::Like->value . '] on nullable value.',
        ];
        yield Operator::NotLike->value => [
            'column', Operator::NotLike, null, 'Invalid operator [' . Operator::NotLike->value . '] on nullable value.',
        ];
    }
}
