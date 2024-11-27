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
 * Class LinkedStack
 *
 * @template T of mixed
 */
final class LinkedStack implements Arrayable, Countable, IteratorAggregate, JsonSerializable
{
    private const int FORWARD = SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP;
    private const int REVERSE = SplDoublyLinkedList::IT_MODE_FIFO | SplDoublyLinkedList::IT_MODE_KEEP;

    private ?string $itemType;
    private SplDoublyLinkedList $list;

    /**
     * Constructs LinkedStack
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
        $this->list->setIteratorMode(self::FORWARD);
    }

    /**
     * Creates LinkedStack instance.
     *
     * @return LinkedStack<T>|LinkedStack
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
     * Retrieves the height.
     */
    public function height(): int
    {
        return count($this->list);
    }

    /**
     * Pushes an item to the top of the stack.
     *
     * @param T $item
     *
     * @phpstan-assert T $item
     */
    public function push(mixed $item): void
    {
        assert(Validate::isType($item, $this->itemType));
        $this->list->push($item);
    }

    /**
     * Removes and returns the item on the top of the stack.
     *
     * @return T
     *
     * @throws UnderflowException When the stack is empty
     */
    public function pop(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException('Stack underflow');
        }

        return $this->list->pop();
    }

    /**
     * Retrieves the top item without removal
     *
     * @return T
     *
     * @throws UnderflowException When the stack is empty
     */
    public function top(): mixed
    {
        if ($this->isEmpty()) {
            throw new UnderflowException('Stack underflow');
        }

        return $this->list->top();
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
     * @return LinkedStack<U>|LinkedStack
     */
    public function map(callable $callback, ?string $itemType = null): self
    {
        $stack = self::of($itemType);

        $this->list->setIteratorMode(self::REVERSE);
        foreach ($this->list as $index => $item) {
            $stack->push(call_user_func($callback, $item, $index));
        }
        $this->list->setIteratorMode(self::FORWARD);

        return $stack;
    }

    /**
     * Creates a collection from items that pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return LinkedStack<T>|LinkedStack
     */
    public function filter(callable $predicate): self
    {
        $stack = self::of($this->itemType);

        $this->list->setIteratorMode(self::REVERSE);
        foreach ($this->list as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $stack->push($item);
            }
        }
        $this->list->setIteratorMode(self::FORWARD);

        return $stack;
    }

    /**
     * Creates a collection from items that fail a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return LinkedStack<T>|LinkedStack
     */
    public function reject(callable $predicate): self
    {
        $stack = self::of($this->itemType);

        $this->list->setIteratorMode(self::REVERSE);
        foreach ($this->list as $index => $item) {
            if (!call_user_func($predicate, $item, $index)) {
                $stack->push($item);
            }
        }
        $this->list->setIteratorMode(self::FORWARD);

        return $stack;
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
     * @return LinkedStack<T>[]|LinkedStack[]
     */
    public function partition(callable $predicate): array
    {
        $stack1 = self::of($this->itemType);
        $stack2 = self::of($this->itemType);

        $this->list->setIteratorMode(self::REVERSE);
        foreach ($this->list as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $stack1->push($item);
            } else {
                $stack2->push($item);
            }
        }
        $this->list->setIteratorMode(self::FORWARD);

        return [$stack1, $stack2];
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