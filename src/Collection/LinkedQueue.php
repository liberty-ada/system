<?php

declare(strict_types=1);

namespace Liberty\System\Collection;

use Countable;
use IteratorAggregate;
use IteratorIterator;
use JsonSerializable;
use Liberty\System\Exception\UnderflowException;
use Liberty\System\Type\Arrayable;
use Liberty\System\Utility\Validate;
use SplDoublyLinkedList;
use Traversable;

/**
 * Class LinkedQueue
 *
 * @template T of mixed
 */
final class LinkedQueue implements Arrayable, Countable, IteratorAggregate, JsonSerializable
{
    private ?string $itemType;
    private SplDoublyLinkedList $list;

    /**
     * Constructs LinkedQueue
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
     * Creates LinkedQueue instance.
     *
     * @return LinkedQueue<T>|LinkedQueue
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
     * Adds an item to the end of the queue.
     *
     * @param T $item
     *
     * @phpstan-assert T $item
     */
    public function enqueue(mixed $item): void
    {
        assert(Validate::isType($item, $this->itemType));
        $this->list->push($item);
    }

    /**
     * Removes and returns the item at the front of the queue.
     *
     * @return T
     *
     * @throws UnderflowException When the queue is empty
     */
    public function dequeue(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException('Queue underflow');
        }

        return $this->list->shift();
    }

    /**
     * Retrieves the front item without removal
     *
     * @return T
     *
     * @throws UnderflowException When the queue is empty
     */
    public function front(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException('Queue underflow');
        }

        return $this->list->bottom();
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
     * @return LinkedQueue<U>|LinkedQueue
     */
    public function map(callable $callback, ?string $itemType = null): self
    {
        $queue = self::of($itemType);

        foreach ($this->list as $index => $item) {
            $queue->enqueue(call_user_func($callback, $item, $index));
        }

        return $queue;
    }

    /**
     * Creates a collection from items that pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return LinkedQueue<T>|LinkedQueue
     */
    public function filter(callable $predicate): self
    {
        $queue = self::of($this->itemType);

        foreach ($this->list as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $queue->enqueue($item);
            }
        }

        return $queue;
    }

    /**
     * Creates a collection from items that fail a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return LinkedQueue<T>|LinkedQueue
     */
    public function reject(callable $predicate): self
    {
        $queue = self::of($this->itemType);

        foreach ($this->list as $index => $item) {
            if (!call_user_func($predicate, $item, $index)) {
                $queue->enqueue($item);
            }
        }

        return $queue;
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
     * @return LinkedQueue<T>[]|LinkedQueue[]
     */
    public function partition(callable $predicate): array
    {
        $queue1 = self::of($this->itemType);
        $queue2 = self::of($this->itemType);

        foreach ($this->list as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $queue1->enqueue($item);
            } else {
                $queue2->enqueue($item);
            }
        }

        return [$queue1, $queue2];
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
     * @inheritDoc
     *
     * @return array|T[]
     */
    public function toArray(): array
    {
        $items = [];

        foreach ($this->list as $item) {
            $items[] = $item;
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
