<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection;

use AssertionError;
use Liberty\System\Collection\LinkedQueue;
use Liberty\System\Exception\UnderflowException;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkedQueue::class)]
class LinkedQueueTest extends UnitTestCase
{
    public function test_that_it_is_empty_by_default(): void
    {
        self::assertTrue(LinkedQueue::of('int')->isEmpty());
    }

    public function test_that_item_type_returns_expected_value(): void
    {
        $itemType = 'string';
        $queue = LinkedQueue::of($itemType);

        self::assertSame($itemType, $queue->itemType());
    }

    public function test_that_adding_items_affects_count(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $queue->enqueue($i);
        }

        self::assertCount(10, $queue);
    }

    public function test_that_adding_items_affects_length(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $queue->enqueue($i);
        }

        self::assertSame(10, $queue->length());
    }

    public function test_that_dequeue_returns_expected_item(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $queue->enqueue($i);
        }

        $output = [];

        foreach ($items as $i) {
            $output[] = $queue->dequeue();
        }

        self::assertSame($items, $output);
    }

    public function test_that_dequeue_returns_item_with_removal(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $queue->enqueue($i);
        }

        $queue->dequeue();

        self::assertCount(9, $queue);
    }

    public function test_that_front_returns_item_without_removal(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $queue->enqueue($i);
        }

        $queue->front();

        self::assertCount(10, $queue);
    }

    public function test_that_mixing_add_remove_operations_keeps_order(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 99);

        foreach ($items as $i) {
            $queue->enqueue($i);
            if ($i % 2 === 0) {
                $queue->dequeue();
            }
        }

        $remaining = [];

        for ($i = 0; $i < 50; $i++) {
            $remaining[] = $queue->dequeue();
        }

        self::assertSame(range(50, 99), $remaining);
    }

    public function test_that_it_is_traversable(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $queue->enqueue($i);
        }

        $output = [];

        foreach ($queue as $item) {
            $output[] = $item;
        }

        self::assertSame($items, $output);
    }

    public function test_that_to_array_returns_expected_value(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $queue->enqueue($i);
        }

        self::assertSame($items, $queue->toArray());
    }

    public function test_that_clone_include_nested_collection(): void
    {
        $queue = LinkedQueue::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $queue->enqueue($i);
        }

        $copy = clone $queue;

        while (!$queue->isEmpty()) {
            $queue->dequeue();
        }

        self::assertSame($items, $copy->toArray());
    }

    public function test_that_each_calls_callback_with_each_item(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        $output = LinkedQueue::of('string');

        $queue->each(function ($item) use ($output) {
            $output->enqueue($item);
        });

        $data = [];

        foreach ($output as $item) {
            $data[] = $item;
        }

        self::assertSame(['foo', 'bar', 'baz'], $data);
    }

    public function test_that_map_returns_expected_queue(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        $output = $queue->map(function ($item) {
            return strlen($item);
        }, 'int');

        $data = [];

        foreach ($output as $item) {
            $data[] = $item;
        }

        self::assertSame([3, 3, 3], $data);
    }

    public function test_that_filter_returns_expected_queue(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        $output = $queue->filter(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        $data = [];

        foreach ($output as $item) {
            $data[] = $item;
        }

        self::assertSame(['bar', 'baz'], $data);
    }

    public function test_that_reject_returns_expected_queue(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        $output = $queue->reject(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        $data = [];

        foreach ($output as $item) {
            $data[] = $item;
        }

        self::assertSame(['foo'], $data);
    }

    public function test_that_any_returns_true_when_an_item_passes_test(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        self::assertTrue($queue->any(function ($item) {
            return $item === 'foo';
        }));
    }

    public function test_that_any_returns_false_when_no_item_passes_test(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        self::assertFalse($queue->any(function ($item) {
            return $item === 'buz';
        }));
    }

    public function test_that_every_returns_true_when_all_items_pass_test(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        self::assertTrue($queue->every(function ($item) {
            return strlen($item) === 3;
        }));
    }

    public function test_that_every_returns_false_when_an_item_fails_test(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        self::assertFalse($queue->every(function ($item) {
            return substr($item, 0, 1) === 'b';
        }));
    }

    public function test_that_partition_returns_expected_queues(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        $parts = $queue->partition(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        $data1 = [];

        foreach ($parts[0] as $item) {
            $data1[] = $item;
        }

        $data2 = [];

        foreach ($parts[1] as $item) {
            $data2[] = $item;
        }

        self::assertTrue($data1 === ['bar', 'baz'] && $data2 === ['foo']);
    }

    public function test_that_it_is_json_encodable(): void
    {
        $queue = LinkedQueue::of('string');
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $queue->enqueue('baz');

        self::assertSame('["foo","bar","baz"]', json_encode($queue));
    }

    public function test_that_enqueue_triggers_assert_error_for_invalid_item_type(): void
    {
        self::expectException(AssertionError::class);

        LinkedQueue::of('int')->enqueue('string');
    }

    public function test_that_dequeue_throws_exception_when_empty(): void
    {
        self::expectException(UnderflowException::class);

        LinkedQueue::of('int')->dequeue();
    }

    public function test_that_front_throws_exception_when_empty(): void
    {
        self::expectException(UnderflowException::class);

        LinkedQueue::of('int')->front();
    }
}
