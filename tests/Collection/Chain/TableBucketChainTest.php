<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection\Chain;

use Liberty\System\Collection\Chain\TableBucketChain;
use Liberty\System\Exception\SystemException;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TableBucketChain::class)]
class TableBucketChainTest extends UnitTestCase
{
    public function test_that_it_is_empty_by_default(): void
    {
        $chain = new TableBucketChain();

        self::assertTrue($chain->isEmpty());
    }

    public function test_that_duplicate_keys_do_not_affect_count(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');
        $chain->set('baz', 'buz');
        $chain->set('foo', 'bar');

        self::assertSame(2, count($chain));
    }

    public function test_that_get_returns_expected_value_for_key(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');
        $chain->set('baz', 'buz');

        self::assertSame('bar', $chain->get('foo'));
    }

    public function test_that_has_returns_true_when_key_is_in_the_chain(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');

        self::assertTrue($chain->has('foo'));
    }

    public function test_that_has_returns_false_when_key_is_not_in_the_chain(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');

        self::assertFalse($chain->has('baz'));
    }

    public function test_that_has_returns_false_after_key_is_removed(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');
        $chain->remove('foo');

        self::assertFalse($chain->has('foo'));
    }

    public function test_that_remove_returns_true_when_key_removed(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');

        self::assertTrue($chain->remove('foo'));
    }

    public function test_that_remove_returns_false_when_key_not_removed(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');

        self::assertFalse($chain->remove('bar'));
    }

    public function test_that_it_is_iterable_forward(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');
        $chain->set('baz', 'buz');
        $chain->set('boz', 'foz');

        for ($chain->rewind(); $chain->valid(); $chain->next()) {
            if ($chain->key() === 'baz') {
                self::assertSame('buz', $chain->current());
            }
        }
    }

    public function test_that_it_is_iterable_in_reverse(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');
        $chain->set('baz', 'buz');
        $chain->set('boz', 'foz');

        for ($chain->end(); $chain->valid(); $chain->prev()) {
            if ($chain->key() === 'baz') {
                self::assertSame('buz', $chain->current());
            }
        }
    }

    public function test_that_it_does_not_iterate_beyond_start(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');
        $chain->set('baz', 'buz');
        $chain->set('boz', 'foz');

        for ($chain->end(); $chain->valid(); $chain->prev()) {
            $chain->current();
        }

        $chain->prev();

        self::assertNull($chain->current());
    }

    public function test_that_it_does_not_iterate_beyond_end(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');
        $chain->set('baz', 'buz');
        $chain->set('boz', 'foz');

        for ($chain->rewind(); $chain->valid(); $chain->next()) {
            $chain->current();
        }

        $chain->next();

        self::assertNull($chain->current());
    }

    public function test_that_calling_key_without_valid_item_returns_null(): void
    {
        $chain = new TableBucketChain();

        self::assertNull($chain->key());
    }

    public function test_that_calling_current_without_valid_item_returns_null(): void
    {
        $chain = new TableBucketChain();

        self::assertNull($chain->current());
    }

    public function test_that_clone_include_nested_collection(): void
    {
        $chain = new TableBucketChain();
        $chain->set('foo', 'bar');
        $chain->set('baz', 'buz');
        $chain->set('boz', 'foz');

        $copy = clone $chain;
        $chain->remove('foo');
        $chain->remove('baz');
        $chain->remove('boz');

        self::assertTrue(
            $copy->has('foo')
            && $copy->has('baz')
            && $copy->has('boz')
        );
    }

    public function test_that_get_throws_exception_for_key_not_found(): void
    {
        self::expectException(SystemException::class);

        $chain = new TableBucketChain();
        $chain->get('foo');
    }
}
