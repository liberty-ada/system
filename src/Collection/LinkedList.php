<?php

declare(strict_types=1);

namespace Liberty\System\Collection;

use Closure;
use Countable;
use IteratorAggregate;
use IteratorIterator;
use JsonSerializable;
use Liberty\System\Exception\SystemException;
use Liberty\System\Type\Arrayable;
use Liberty\System\Utility\Validate;
use SplDoublyLinkedList;
use Traversable;

/**
 * Class LinkedList
 *
 * @template T of mixed
 */
final class LinkedList implements Arrayable, Countable, IteratorAggregate, JsonSerializable
{
    private ?string $itemType;
    private SplDoublyLinkedList $list;

    /**
     * Constructs LinkedList
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
        $this->list = new SplDoublyLinkedList();
        $mode = SplDoublyLinkedList::IT_MODE_FIFO | SplDoublyLinkedList::IT_MODE_KEEP;
        $this->list->setIteratorMode($mode);
    }

    /**
     * Creates LinkedList instance.
     *
     * @return LinkedList<T>|LinkedList
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
        return $this->list->isEmpty();
    }

    /**
     * Retrieves the count.
     */
    public function count(): int
    {
        return count($this->list);
    }

    /**
     * Retrieves the length.
     */
    public function length(): int
    {
        return count($this->list);
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
        $this->list->push($item);
    }

    /**
     * Creates new instance of the same type with items.
     *
     * @param list<T> $items
     *
     * @return LinkedList<T>|LinkedList
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
     * @return LinkedList<T>|LinkedList
     */
    public function sort(callable $comparator): self
    {
        $list = self::of($this->itemType);
        $items = $this->toArray();

        usort($items, $comparator);

        foreach ($items as $item) {
            $list->add($item);
        }

        return $list;
    }

    /**
     * Creates an instance in reverse order.
     *
     * @return LinkedList<T>|LinkedList
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
     * @return LinkedList<T>|LinkedList
     */
    public function unique(?callable $callback = null): self
    {
        if ($callback === null) {
            $list = self::of($this->itemType);

            $items = array_values(array_unique($this->toArray(), SORT_REGULAR));

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
     * @return LinkedList<T>|LinkedList
     */
    public function slice(int $index, ?int $length = null): self
    {
        $list = self::of($this->itemType);

        $items = array_slice($this->toArray(), $index, $length);

        foreach ($items as $item) {
            $list->add($item);
        }

        return $list;
    }

    /**
     * Creates a paginated collection.
     *
     * @return LinkedList<T>|LinkedList
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
        foreach ($this->list as $index => $item) {
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
     * @return LinkedList<U>|LinkedList
     */
    public function map(callable $callback, ?string $itemType = null): self
    {
        $list = self::of($itemType);

        foreach ($this->list as $index => $item) {
            $list->add(call_user_func($callback, $item, $index));
        }

        return $list;
    }

    /**
     * Creates a collection from items that pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return LinkedList<T>|LinkedList
     */
    public function filter(callable $predicate): self
    {
        $list = self::of($this->itemType);

        foreach ($this->list as $index => $item) {
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
     * @return LinkedList<T>|LinkedList
     */
    public function reject(callable $predicate): self
    {
        $list = self::of($this->itemType);

        foreach ($this->list as $index => $item) {
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
     * @return LinkedList<T>[]|LinkedList[]
     */
    public function partition(callable $predicate): array
    {
        $list1 = self::of($this->itemType);
        $list2 = self::of($this->itemType);

        foreach ($this->list as $index => $item) {
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

            return $this->list->bottom();
        }

        foreach ($this->list as $index => $item) {
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

            return $this->list->top();
        }

        $reverse = SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP;
        $forward = SplDoublyLinkedList::IT_MODE_FIFO | SplDoublyLinkedList::IT_MODE_KEEP;

        $this->list->setIteratorMode($reverse);

        foreach ($this->list as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $this->list->setIteratorMode($forward);

                return $item;
            }
        }

        $this->list->setIteratorMode($forward);

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
            $key = array_search($object, $this->toArray(), $strict = true);

            if ($key === false) {
                return null;
            }

            return $key;
        }

        foreach ($this->list as $index => $item) {
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
            $key = array_search($object, array_reverse($this->toArray(), true), $strict = true);

            if ($key === false) {
                return null;
            }

            return $key;
        }

        $reverse = SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP;
        $forward = SplDoublyLinkedList::IT_MODE_FIFO | SplDoublyLinkedList::IT_MODE_KEEP;

        $this->list->setIteratorMode($reverse);

        foreach ($this->list as $index => $item) {
            if (call_user_func($object, $item, $index)) {
                $this->list->setIteratorMode($forward);

                return $index;
            }
        }

        $this->list->setIteratorMode($forward);

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

            foreach ($this->list as $index => $item) {
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

            foreach ($this->list as $index => $item) {
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

        foreach ($this->list as $index => $item) {
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
        foreach ($this->list as $index => $item) {
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
        return in_array($item, $this->toArray(), $strict);
    }

    /**
     * Checks if any items pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     */
    public function any(callable $predicate): bool
    {
        foreach ($this->list as $index => $item) {
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
        foreach ($this->list as $index => $item) {
            if (!call_user_func($predicate, $item, $index)) {
                return false;
            }
        }

        return true;
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

        return $this->list->bottom();
    }

    /**
     * Retrieves the tail of the list.
     *
     * @return LinkedList<T>|LinkedList
     *
     * @throws SystemException When the list is empty
     */
    public function tail(): LinkedList
    {
        if ($this->isEmpty()) {
            throw new SystemException('List underflow');
        }

        $items = $this->toArray();
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
        $this->list->rewind();
    }

    /**
     * Moves the internal iterator to the end of the list.
     */
    public function end(): void
    {
        if (!$this->valid()) {
            $this->rewind();
        }

        while ($this->valid()) {
            $this->next();
        }

        $this->prev();
    }

    /**
     * Checks if the internal iterator is at a valid index.
     */
    public function valid(): bool
    {
        return $this->list->valid();
    }

    /**
     * Moves the internal iterator to the next item in the list.
     */
    public function next(): void
    {
        $this->list->next();
    }

    /**
     * Moves the internal iterator to the previous item in the list.
     */
    public function prev(): void
    {
        $this->list->prev();
    }

    /**
     * Retrieves the index at the internal iterator position.
     */
    public function key(): ?int
    {
        return is_int($this->list->key()) ? $this->list->key() : null;
    }

    /**
     * Retrieves the item at the internal iterator position.
     */
    public function current(): mixed
    {
        if ($this->key() === null) {
            return null;
        }

        return $this->list->current();
    }

    /**
     * @inheritDoc
     *
     * @return array|T[]
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->getIterator() as $item) {
            $item[] = $item;
        }

        return $items;
    }

    /**
     * Retrieves the iterator.
     *
     * @return Traversable<T>|Traversable
     */
    public function getIterator(): Traversable
    {
        return new IteratorIterator($this->list);
    }

    /**
     * Retrieves an array representation for JSON encoding.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Handles deep cloning.
     */
    public function __clone(): void
    {
        $list = clone $this->list;
        $this->list = $list;
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
}
