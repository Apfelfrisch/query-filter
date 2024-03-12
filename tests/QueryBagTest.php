<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests;

use Apfelfrisch\QueryFilter\Exceptions\QueryFilterException;
use Apfelfrisch\QueryFilter\QueryBag;

final class QueryBagTest extends TestCase
{
    public function test_parsing_parameters_from_url_like_string(): void
    {
        $queryBag = QueryBag::fromUrl('/user?id=1');
        $this->assertSame('1', $queryBag->get('id'));

        $queryBag = QueryBag::fromUrl('id=1');
        $this->assertSame('1', $queryBag->get('id'));
    }

    public function test_getting_values_from_query_bag(): void
    {
        $queryBag = new QueryBag(['string-key' => 'string']);

        $this->assertSame('string', $queryBag->get('string-key'));
        $this->assertSame('string', $queryBag->getString('string-key'));
        $this->assertSame(['string'], $queryBag->getArray('string-key'));
    }

    public function test_cheching_if_value_is_existent(): void
    {
        $queryBag = new QueryBag(['existent-string-key' => 'string']);

        $this->assertSame(true, $queryBag->has('existent-string-key'));
        $this->assertSame(false, $queryBag->has('non-existent-string-key'));
    }

    public function test_throwing_exception_on_non_scalar_value(): void
    {
        $queryBag = new QueryBag(['string-key' => (object)['string']]);

        $this->expectException(QueryFilterException::class);

        $this->assertSame('string', $queryBag->get('string-key'));
    }
}
