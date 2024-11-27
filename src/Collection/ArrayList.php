<?php

declare(strict_types=1);

namespace Liberty\System\Collection;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Liberty\System\Exception\SystemException;
use Liberty\System\Type\Arrayable;
use Liberty\System\Utility\Validate;
use Traversable;

/**
 * Class ArrayList
 *
 * @template T of mixed
 */
final class ArrayList implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    private ?string $itemType;
    private array $items = [];

    /**
     * Constructs ArrayList
     *
     * If a type is not provided, the item type is dynamic.
     *
     * The type can be any fully-qualified class or interface name,
     * or one of the following type strings:
     * [array, object, bool, int, float, string, callable]
     */
    public function __construct(?string $itemType = null)
    {
        $this->setItemType($itemType);
    }

    /**
     * Creates ArrayList instance.
     *
     * @return ArrayList<T>|ArrayList
     */
    public static function of(?string $itemType = null): self
    {
        return new self($itemType);
    }

    /**
     * Retrieves the item type.
     *
     * Returns null if the item type is dynamic.
     */
    public function itemType(): ?string
    {
        return $this->itemType;
    }

    /**
     * Checks if empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Retrieves the count.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Retrieves the length.
     */
    public function length(): int
    {
        return count($this->items);
    }

    /**
     * Appends an item to the list.
     *
     * @param T $item
     *
     * @phpstan-assert T $item
     */
    public function add(mixed $item): void
    {
        assert(Validate::isType($item, $this->itemType));
        $this->items[] = $item;
    }

    /**
     * Creates new instance of the same type with items.
     *
     * @param list<T> $items
     *
     * @return ArrayList<T>|ArrayList
     */
    public function replace(iterable $items = []): self
    {
        $list = self::of($this->itemType);

        foreach ($items as $item) {
            $list->add($item);
        }

        return $list;
    }

    /**
     * Creates an instance sorted using a comparator function.
     *
     * @return ArrayList<T>|ArrayList
     */
    public function sort(callable $comparator): self
    {
        $list = self::of($this->itemType);
        $items = $this->items;

        usort($items, $comparator);

        foreach ($items as $item) {
            $list->add($item);
        }

        return $list;
    }

    /**
     * Creates an instance in reverse order.
     *
     * @return ArrayList<T>|ArrayList
     */
    public function reverse(): self
    {
        $list = self::of($this->itemType);

        for ($this->end(); $this->valid(); $this->prev()) {
            $list->add($this->current());
        }

        return $list;
    }

    /**
     * Creates a collection with unique items.
     *
     * Optional callback should return a string value for equality comparison.
     *
     * @param callable(T, int): string|null $callback
     *
     * @return ArrayList<T>|ArrayList
     */
    public function unique(?callable $callback = null): self
    {
        if ($callback === null) {
            $list = self::of($this->itemType);

            $items = array_values(array_unique($this->items, SORT_REGULAR));

            foreach ($items as $item) {
                $list->add($item);
            }

            return $list;
        }

        $set = [];

        return $this->filter(function ($item, $index) use ($callback, &$set) {
            $hash = call_user_func($callback, $item, $index);

            if (isset($set[$hash])) {
                return false;
            }

            $set[$hash] = true;

            return true;
        });
    }

    /**
     * Creates a collection from a slice of items.
     *
     * @return ArrayList<T>|ArrayList
     */
    public function slice(int $index, ?int $length = null): self
    {
        $list = self::of($this->itemType);

        $items = array_slice($this->items, $index, $length);

        foreach ($items as $item) {
            $list->add($item);
        }

        return $list;
    }

    /**
     * Creates a paginated collection.
     *
     * @return ArrayList<T>|ArrayList
     */
    public function page(int $page, int $perPage): self
    {
        return $this->slice(($page - 1) * $perPage, $perPage);
    }

    /**
     * Applies a callback function to every item.
     *
     * @param callable(T, int): void $callback
     */
    public function each(callable $callback): void
    {
        foreach ($this->items as $index => $item) {
            call_user_func($callback, $item, $index);
        }
    }

    /**
     * Creates a collection from the results of a function.
     *
     * @template U
     *
     * @param callable(T, int): U         $callback
     * @param class-string<U>|string|null $itemType
     *
     * @return ArrayList<U>|ArrayList
     */
    public function map(callable $callback, ?string $itemType = null): self
    {
        $list = self::of($itemType);

        foreach ($this->items as $index => $item) {
            $list->add(call_user_func($callback, $item, $index));
        }

        return $list;
    }

    /**
     * Creates a collection from items that pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return ArrayList<T>|ArrayList
     */
    public function filter(callable $predicate): self
    {
        $list = self::of($this->itemType);

        foreach ($this->items as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $list->add($item);
            }
        }

        return $list;
    }

    /**
     * Creates a collection from items that fail a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return ArrayList<T>|ArrayList
     */
    public function reject(callable $predicate): self
    {
        $list = self::of($this->itemType);

        foreach ($this->items as $index => $item) {
            if (!call_user_func($predicate, $item, $index)) {
                $list->add($item);
            }
        }

        return $list;
    }

    /**
     * Creates two collections based on a truth test.
     *
     * Items that pass the truth test are placed in the first collection.
     *
     * Items that fail the truth test are placed in the second collection.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return ArrayList<T>[]|ArrayList[]
     */
    public function partition(callable $predicate): array
    {
        $list1 = self::of($this->itemType);
        $list2 = self::of($this->itemType);

        foreach ($this->items as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $list1->add($item);
            } else {
                $list2->add($item);
            }
        }

        return [$list1, $list2];
    }

    /**
     * Retrieves the first value.
     *
     * Optionally retrieves the first value that passes a truth test.
     *
     * @param callable(T, int): bool|null $predicate
     * @param T|null                      $default
     *
     * @return T|null
     */
    public function first(?callable $predicate = null, mixed $default = null): mixed
    {
        if ($predicate === null) {
            if ($this->isEmpty()) {
                return $default;
            }

            $key = array_key_first($this->items);

            return $this->items[$key];
        }

        foreach ($this->items as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * Retrieves the last value.
     *
     * Optionally retrieves the last value that passes a truth test.
     *
     * @param callable(T, int): bool|null $predicate
     * @param T|null                      $default
     *
     * @return T|null
     */
    public function last(?callable $predicate = null, mixed $default = null): mixed
    {
        if ($predicate === null) {
            if ($this->isEmpty()) {
                return $default;
            }

            $key = array_key_last($this->items);

            return $this->items[$key];
        }

        foreach (array_reverse($this->items, true) as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * Retrieves the first index of the given item.
     *
     * Optionally retrieves the first index that passes a truth test.
     *
     * @param mixed|callable(T, int): bool $object The search item or a predicate function
     */
    public function indexOf(mixed $object): ?int
    {
        if (!($object instanceof Closure)) {
            $key = array_search($object, $this->items, $strict = true);

            if ($key === false) {
                return null;
            }

            return $key;
        }

        foreach ($this->items as $index => $item) {
            if (call_user_func($object, $item, $index)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Retrieves the last index of the given item.
     *
     * Optionally retrieves the last index that passes a truth test.
     *
     * @param mixed|callable(T, int): bool $object The search item or a predicate function
     */
    public function lastIndexOf(mixed $object): ?int
    {
        if (!($object instanceof Closure)) {
            $key = array_search($object, array_reverse($this->items, true), $strict = true);

            if ($key === false) {
                return null;
            }

            return $key;
        }

        foreach (array_reverse($this->items, true) as $index => $item) {
            if (call_user_func($object, $item, $index)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Retrieves the maximum value in the list.
     *
     * The callback should return a value to compare.
     *
     * @param callable(T, int): mixed|null $callback
     *
     * @return T|null
     */
    public function max(?callable $callback = null): mixed
    {
        if ($callback !== null) {
            $maxItem = null;
            $max = null;

            foreach ($this->items as $index => $item) {
                $field = call_user_func($callback, $item, $index);
                if ($max === null || $field > $max) {
                    $max = $field;
                    $maxItem = $item;
                }
            }

            return $maxItem;
        }

        return $this->reduce(function ($accumulator, $item) {
            if ($accumulator === null || $item > $accumulator) {
                return $item;
            }

            return $accumulator;
        });
    }

    /**
     * Retrieves the minimum value in the list.
     *
     * The callback should return a value to compare.
     *
     * @param callable(T, int): mixed|null $callback
     *
     * @return T|null
     */
    public function min(?callable $callback = null): mixed
    {
        if ($callback !== null) {
            $minItem = null;
            $min = null;

            foreach ($this->items as $index => $item) {
                $field = call_user_func($callback, $item, $index);
                if ($min === null || $field < $min) {
                    $min = $field;
                    $minItem = $item;
                }
            }

            return $minItem;
        }

        return $this->reduce(function ($accumulator, $item) {
            if ($accumulator === null || $item < $accumulator) {
                return $item;
            }

            return $accumulator;
        });
    }

    /**
     * Reduces the collection to a single value.
     *
     * @template V
     *
     * @param callable(V, T, int): V $callback
     * @param V|null                 $initial
     *
     * @return V|null
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $accumulator = $initial;

        foreach ($this->items as $index => $item) {
            $accumulator = call_user_func($callback, $accumulator, $item, $index);
        }

        return $accumulator;
    }

    /**
     * Retrieves the sum of the collection.
     *
     * The callback should return a value to sum.
     *
     * @param callable(T, int): (int|float)|null $callback
     */
    public function sum(?callable $callback = null): int|float|null
    {
        if ($this->isEmpty()) {
            return null;
        }

        if ($callback === null) {
            $callback = function ($item) {
                return $item;
            };
        }

        return $this->reduce(function ($total, $item, $index) use ($callback) {
            return $total + call_user_func($callback, $item, $index);
        }, 0);
    }

    /**
     * Retrieves the average of the collection.
     *
     * The callback should return a value to sum.
     *
     * @param callable(T, int): (int|float)|null $callback
     */
    public function average(?callable $callback = null): int|float|null
    {
        if ($this->isEmpty()) {
            return null;
        }

        $count = $this->count();

        return $this->sum($callback) / $count;
    }

    /**
     * Retrieves the first item that passes a truth test.
     *
     * Returns null if no item passes the test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return T|null
     */
    public function find(callable $predicate): mixed
    {
        foreach ($this->items as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Checks if an item is in the list.
     *
     * @param T    $item   The item
     * @param bool $strict Whether to search with strict typing
     */
    public function contains(mixed $item, bool $strict = true): bool
    {
        return in_array($item, $this->items, $strict);
    }

    /**
     * Checks if any items pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     */
    public function any(callable $predicate): bool
    {
        foreach ($this->items as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if all items pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     */
    public function every(callable $predicate): bool
    {
        foreach ($this->items as $index => $item) {
            if (!call_user_func($predicate, $item, $index)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieves an item at a specific index.
     *
     * @return T
     *
     * @throws SystemException When the requested index is out of bounds
     */
    public function get(int $index): mixed
    {
        $index = $this->getRealOffset($index);

        return $this->items[$index];
    }

    /**
     * Sets an item at a specific index.
     *
     * @param int $index
     * @param T   $item
     *
     * @throws SystemException When the requested index is out of bounds
     *
     * @phpstan-assert T $item
     */
    public function set(int $index, mixed $item): void
    {
        assert(Validate::isType($item, $this->itemType()));

        $index = $this->getRealOffset($index);

        $this->items[$index] = $item;
    }

    /**
     * Checks if an index is in bounds.
     */
    public function has(int $index): bool
    {
        $count = count($this->items);

        if ($index < -$count || $index > $count - 1) {
            return false;
        }

        return true;
    }

    /**
     * Removes an item at a specific index.
     */
    public function remove(int $index): void
    {
        $count = count($this->items);

        if ($index < -$count || $index > $count - 1) {
            return;
        }

        if ($index < 0) {
            $index += $count;
        }

        array_splice($this->items, $index, 1);
    }

    /**
     * Retrieves an item at a specific index.
     *
     * @param int $offset
     *
     * @throws SystemException When the requested index is out of bounds
     *
     * @phpstan-assert int $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        assert(Validate::isInt($offset));

        return $this->get($offset);
    }

    /**
     * Sets an item at a specific index.
     *
     * @param int $offset
     * @param T   $value
     *
     * @throws SystemException When the requested index is out of bounds
     *
     * @phpstan-assert int|null $offset
     * @phpstan-assert T        $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->add($value);
        }

        assert(Validate::isInt($offset));
        $this->set($offset, $value);
    }

    /**
     * Checks if an index is in bounds.
     *
     * @phpstan-assert int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        assert(Validate::isInt($offset));

        return $this->has($offset);
    }

    /**
     * Removes an item at a specific index.
     *
     * @phpstan-assert int $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        assert(Validate::isInt($offset));
        $this->remove($offset);
    }

    /**
     * Retrieves the head of the list.
     *
     * @return T
     *
     * @throws SystemException When the list is empty
     */
    public function head(): mixed
    {
        if ($this->isEmpty()) {
            throw new SystemException('List underflow');
        }

        $key = array_key_first($this->items);

        return $this->items[$key];
    }

    /**
     * Retrieves the tail of the list.
     *
     * @return ArrayList<T>|ArrayList
     *
     * @throws SystemException When the list is empty
     */
    public function tail(): ArrayList
    {
        if ($this->isEmpty()) {
            throw new SystemException('List underflow');
        }

        $items = $this->items;
        array_shift($items);

        $list = self::of($this->itemType);

        foreach ($items as $item) {
            $list->add($item);
        }

        return $list;
    }

    /**
     * Moves the internal iterator to the beginning of the list.
     */
    public function rewind(): void
    {
        reset($this->items);
    }

    /**
     * Moves the internal iterator to the end of the list.
     */
    public function end(): void
    {
        end($this->items);
    }

    /**
     * Checks if the internal iterator is at a valid index.
     */
    public function valid(): bool
    {
        return key($this->items) !== null;
    }

    /**
     * Moves the internal iterator to the next item in the list.
     */
    public function next(): void
    {
        next($this->items);
    }

    /**
     * Moves the internal iterator to the previous item in the list.
     */
    public function prev(): void
    {
        prev($this->items);
    }

    /**
     * Retrieves the index at the internal iterator position.
     */
    public function key(): ?int
    {
        return key($this->items);
    }

    /**
     * Retrieves the item at the internal iterator position.
     */
    public function current(): mixed
    {
        if (key($this->items) === null) {
            return null;
        }

        return current($this->items);
    }

    /**
     * @inheritDoc
     *
     * @return array|T[]
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Retrieves the iterator.
     *
     * @return Traversable<T>|Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Retrieves an array representation for JSON encoding.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Sets the item type.
     *
     * If a type is not provided, the item type is dynamic.
     *
     * The type can be any fully-qualified class or interface name,
     * or one of the following type strings:
     * [array, object, bool, int, float, string, callable]
     */
    private function setItemType(?string $itemType = null): void
    {
        $this->itemType = $itemType;
    }

    /**
     * Retrieves the real offset.
     *
     * @throws SystemException When the requested index is out of bounds
     */
    private function getRealOffset(int $index): int
    {
        $count = count($this->items);

        if ($index < -$count || $index > $count - 1) {
            $message = sprintf('Index (%d) out of range[%d, %d]', $index, -$count, $count - 1);
            throw new SystemException($message);
        }

        if ($index < 0) {
            $index += $count;
        }

        return $index;
    }
}
