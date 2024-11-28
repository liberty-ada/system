<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection\Chain\Bucket;

use Liberty\System\Collection\Chain\Bucket\ItemBucket;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ItemBucket::class)]
class ItemBucketTest extends UnitTestCase
{
    public function test_that_constructor_takes_item_argument(): void
    {
        $bucket = new ItemBucket('foo');

        self::assertSame('foo', $bucket->item());
    }

    public function test_that_next_stores_bucket_instance(): void
    {
        $bucket = new ItemBucket('foo');
        $next = new ItemBucket('bar');
        $bucket->setNext($next);

        self::assertSame($next, $bucket->next());
    }

    public function test_that_prev_stores_bucket_instance(): void
    {
        $bucket = new ItemBucket('foo');
        $prev = new ItemBucket('bar');
        $bucket->setPrev($prev);

        self::assertSame($prev, $bucket->prev());
    }
}
