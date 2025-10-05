<?php

declare(strict_types=1);

namespace Apfelfrisch\QueryFilter\Tests;

use Apfelfrisch\QueryFilter\Str;

final class StrTest extends TestCase
{
    public function test_camel_case(): void
    {
        $this->assertSame("camelCase", Str::camel("camel-case"));
        $this->assertSame("camelCase", Str::camel("camel_case"));
    }
}
