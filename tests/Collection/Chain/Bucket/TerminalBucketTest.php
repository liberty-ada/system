<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection\Chain\Bucket;

use Liberty\System\Collection\Chain\Bucket\TerminalBucket;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TerminalBucket::class)]
class TerminalBucketTest extends UnitTestCase
{
    public function test_that_next_stores_bucket_instance(): void
    {
        $bucket = new TerminalBucket();
        $next = new TerminalBucket();
        $bucket->setNext($next);

        self::assertSame($next, $bucket->next());
    }

    public function test_that_prev_stores_bucket_instance(): void
    {
        $bucket = new TerminalBucket();
        $prev = new TerminalBucket();
        $bucket->setPrev($prev);

        self::assertSame($prev, $bucket->prev());
    }
}
