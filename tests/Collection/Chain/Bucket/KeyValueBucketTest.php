<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection\Chain\Bucket;

use Liberty\System\Collection\Chain\Bucket\KeyValueBucket;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(KeyValueBucket::class)]
class KeyValueBucketTest extends UnitTestCase
{
    public function test_that_constructor_takes_key_argument(): void
    {
        $bucket = new KeyValueBucket('foo', 'bar');

        self::assertSame('foo', $bucket->key());
    }

    public function test_that_constructor_takes_value_argument(): void
    {
        $bucket = new KeyValueBucket('foo', 'bar');

        self::assertSame('bar', $bucket->value());
    }

    public function test_that_next_stores_bucket_instance(): void
    {
        $bucket = new KeyValueBucket('foo', 'bar');
        $next = new KeyValueBucket('baz', 'buz');
        $bucket->setNext($next);

        self::assertSame($next, $bucket->next());
    }

    public function test_that_prev_stores_bucket_instance(): void
    {
        $bucket = new KeyValueBucket('foo', 'bar');
        $prev = new KeyValueBucket('baz', 'buz');
        $bucket->setPrev($prev);

        self::assertSame($prev, $bucket->prev());
    }
}
