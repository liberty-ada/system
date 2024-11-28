<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection;

use AssertionError;
use Liberty\System\Collection\LinkedStack;
use Liberty\System\Exception\UnderflowException;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkedStack::class)]
class LinkedStackTest extends UnitTestCase
{
    public function test_that_it_is_empty_by_default(): void
    {
        static::assertTrue(LinkedStack::of('int')->isEmpty());
    }

    public function test_that_item_type_returns_expected_value(): void
    {
        $itemType = 'string';
        $stack = LinkedStack::of($itemType);

        self::assertSame($itemType, $stack->itemType());
    }

    public function test_that_adding_items_affects_count(): void
    {
        $stack = LinkedStack::of('int');

        foreach (range(0, 9) as $i) {
            $stack->push($i);
        }

        static::assertCount(10, $stack);
    }

    public function test_that_adding_items_affects_height(): void
    {
        $stack = LinkedStack::of('int');

        foreach (range(0, 9) as $i) {
            $stack->push($i);
        }

        static::assertSame(10, $stack->height());
    }

    public function test_that_pop_returns_expected_item(): void
    {
        $stack = LinkedStack::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $stack->push($i);
        }

        $output = [];

        foreach ($items as $i) {
            $output[] = $stack->pop();
        }

        static::assertSame($items, array_reverse($output));
    }

    public function test_that_pop_returns_item_with_removal(): void
    {
        $stack = LinkedStack::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $stack->push($i);
        }

        $stack->pop();

        static::assertCount(9, $stack);
    }

    public function test_that_top_returns_item_without_removal(): void
    {
        $stack = LinkedStack::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $stack->push($i);
        }

        $stack->top();

        static::assertCount(10, $stack);
    }

    public function test_that_mixing_add_remove_operations_affects_order(): void
    {
        $stack = LinkedStack::of('int');
        $items = range(0, 99);

        foreach ($items as $i) {
            $stack->push($i);
            if ($i % 2 === 0) {
                $stack->pop();
            }
        }

        $remaining = [];

        for ($i = 0; $i < 50; $i++) {
            $remaining[] = $stack->pop();
        }

        static::assertSame(range(1, 99, 2), array_reverse($remaining));
    }

    public function test_that_it_is_traversable(): void
    {
        $stack = LinkedStack::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $stack->push($i);
        }

        $output = [];

        foreach ($stack as $item) {
            $output[] = $item;
        }

        static::assertSame(array_reverse($items), $output);
    }

    public function test_that_to_array_returns_expected_value(): void
    {
        $stack = LinkedStack::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $stack->push($i);
        }

        static::assertSame(array_reverse($items), $stack->toArray());
    }

    public function test_that_clone_include_nested_collection(): void
    {
        $stack = LinkedStack::of('int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $stack->push($i);
        }

        $copy = clone $stack;

        while (!$stack->isEmpty()) {
            $stack->pop();
        }

        static::assertSame(array_reverse($items), $copy->toArray());
    }

    public function test_that_each_calls_callback_with_each_item(): void
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        $output = LinkedStack::of('string');

        $stack->each(function ($item) use ($output) {
            $output->push($item);
        });

        $data = [];

        foreach ($output as $item) {
            $data[] = $item;
        }

        static::assertSame(['foo', 'bar', 'baz'], $data);
    }

    public function test_that_map_returns_expected_stack(): void
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        $output = $stack->map(function ($item) {
            return strlen($item);
        }, 'int');

        $data = [];

        foreach ($output as $item) {
            $data[] = $item;
        }

        static::assertSame([3, 3, 3], $data);
    }

    public function test_that_filter_returns_expected_stack()
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        $output = $stack->filter(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        $data = [];

        foreach ($output as $item) {
            $data[] = $item;
        }

        static::assertSame(['baz', 'bar'], $data);
    }

    public function test_that_reject_returns_expected_stack()
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        $output = $stack->reject(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        $data = [];

        foreach ($output as $item) {
            $data[] = $item;
        }

        static::assertSame(['foo'], $data);
    }

    public function test_that_any_returns_true_when_an_item_passes_test()
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        static::assertTrue($stack->any(function ($item) {
            return $item === 'foo';
        }));
    }

    public function test_that_any_returns_false_when_no_item_passes_test()
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        static::assertFalse($stack->any(function ($item) {
            return $item === 'buz';
        }));
    }

    public function test_that_every_returns_true_when_all_items_pass_test()
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        static::assertTrue($stack->every(function ($item) {
            return strlen($item) === 3;
        }));
    }

    public function test_that_every_returns_false_when_an_item_fails_test()
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        static::assertFalse($stack->every(function ($item) {
            return substr($item, 0, 1) === 'b';
        }));
    }

    public function test_that_partition_returns_expected_stacks()
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        $parts = $stack->partition(function ($item) {
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

        static::assertTrue($data1 === ['baz', 'bar'] && $data2 === ['foo']);
    }

    public function test_that_it_is_json_encodable()
    {
        $stack = LinkedStack::of('string');
        $stack->push('foo');
        $stack->push('bar');
        $stack->push('baz');

        static::assertSame('["baz","bar","foo"]', json_encode($stack));
    }

    public function test_that_push_triggers_assert_error_for_invalid_item_type()
    {
        static::expectException(AssertionError::class);

        LinkedStack::of('int')->push('string');
    }

    public function test_that_pop_throws_exception_when_empty()
    {
        static::expectException(UnderflowException::class);

        LinkedStack::of('int')->pop();
    }

    public function test_that_top_throws_exception_when_empty()
    {
        static::expectException(UnderflowException::class);

        LinkedStack::of('int')->top();
    }
}
