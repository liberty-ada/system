<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection\Chain;

use Liberty\System\Collection\Chain\SetBucketChain;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SetBucketChain::class)]
class SetBucketChainTest extends UnitTestCase
{
    public function test_that_it_is_empty_by_default(): void
    {
        $chain = new SetBucketChain();

        self::assertTrue($chain->isEmpty());
    }

    public function test_that_duplicate_items_do_not_affect_count(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');
        $chain->add('foo');

        self::assertSame(2, count($chain));
    }

    public function test_that_contains_returns_true_when_item_is_in_the_chain(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');

        self::assertTrue($chain->contains('bar'));
    }

    public function test_that_contains_returns_false_when_item_is_not_in_the_chain(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');

        self::assertFalse($chain->contains('baz'));
    }

    public function test_that_contains_returns_false_after_item_is_removed(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');
        $chain->remove('foo');

        self::assertFalse($chain->contains('foo'));
    }

    public function test_that_remove_returns_true_when_item_removed(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');

        self::assertTrue($chain->remove('foo'));
    }

    public function test_that_remove_returns_false_when_item_not_removed(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');

        self::assertFalse($chain->remove('bar'));
    }

    public function test_that_it_is_iterable_forward(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');
        $chain->add('baz');

        for ($chain->rewind(); $chain->valid(); $chain->next()) {
            if ($chain->key() === 1) {
                self::assertSame('bar', $chain->current());
            }
        }
    }

    public function test_that_it_is_iterable_in_reverse(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');
        $chain->add('baz');

        for ($chain->end(); $chain->valid(); $chain->prev()) {
            if ($chain->key() === 1) {
                self::assertSame('bar', $chain->current());
            }
        }
    }

    public function test_that_it_does_not_iterate_beyond_start(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');
        $chain->add('baz');

        for ($chain->end(); $chain->valid(); $chain->prev()) {
            $chain->current();
        }

        $chain->prev();

        self::assertNull($chain->current());
    }

    public function test_that_it_does_not_iterate_beyond_end(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');
        $chain->add('baz');

        for ($chain->rewind(); $chain->valid(); $chain->next()) {
            $chain->current();
        }

        $chain->next();

        self::assertNull($chain->current());
    }

    public function test_that_calling_key_without_valid_item_returns_null(): void
    {
        $chain = new SetBucketChain();

        self::assertNull($chain->key());
    }

    public function test_that_calling_current_without_valid_item_returns_null(): void
    {
        $chain = new SetBucketChain();

        self::assertNull($chain->current());
    }

    public function test_that_clone_includes_nested_collection(): void
    {
        $chain = new SetBucketChain();
        $chain->add('foo');
        $chain->add('bar');
        $chain->add('baz');

        $copy = clone $chain;
        $chain->remove('foo');
        $chain->remove('bar');
        $chain->remove('baz');

        self::assertTrue(
            $copy->contains('foo')
            && $copy->contains('bar')
            && $copy->contains('baz')
        );
    }
}
