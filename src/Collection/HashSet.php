<?php

declare(strict_types=1);

namespace Liberty\System\Collection;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Liberty\System\Collection\Chain\SetBucketChain;
use Liberty\System\Collection\Iterator\GeneratorIterator;
use Liberty\System\Type\Arrayable;
use Liberty\System\Utility\FastHasher;
use Liberty\System\Utility\Validate;
use Traversable;

/**
 * Class HashSet
 *
 * @template T of mixed
 */
final class HashSet implements Arrayable, Countable, IteratorAggregate, JsonSerializable
{
    private ?string $itemType;
    /** @var array<string, SetBucketChain> */
    private array $buckets = [];
    private int $count = 0;

    /**
     * Constructs HashSet
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
     * Creates HashSet instance.
     *
     * @return HashSet<T>|HashSet
     */
    public static function of(?string $itemType = null): HashSet
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
        return $this->count === 0;
    }

    /**
     * Retrieves the count.
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Adds an item.
     *
     * @param T          $item
     *
     * @phpstan-assert T $item
     */
    public function add(mixed $item): void
    {
        assert(Validate::isType($item, $this->itemType));

        $hash = FastHasher::hash($item);

        if (!isset($this->buckets[$hash])) {
            $this->buckets[$hash] = new SetBucketChain();
        }

        if ($this->buckets[$hash]->add($item)) {
            $this->count++;
        }
    }

    /**
     * Checks if an item is in the set.
     *
     * @param T $item
     */
    public function contains(mixed $item): bool
    {
        $hash = FastHasher::hash($item);

        if (!isset($this->buckets[$hash])) {
            return false;
        }

        return $this->buckets[$hash]->contains($item);
    }

    /**
     * Removes an item.
     *
     * @param T $item
     */
    public function remove(mixed $item): void
    {
        $hash = FastHasher::hash($item);

        if (isset($this->buckets[$hash])) {
            if ($this->buckets[$hash]->remove($item)) {
                $this->count--;
                if ($this->buckets[$hash]->isEmpty()) {
                    unset($this->buckets[$hash]);
                }
            }
        }
    }

    /**
     * Retrieves the symmetric difference.
     *
     * Creates a new set that contains items in the current set that are not in
     * the provided set, as well as items in the provided set that are not in
     * the current set.
     *
     * A ∆ B = {x : (x ∈ A) ⊕ (x ∈ B)}
     *
     * @phpstan-assert HashSet<T> $other
     */
    public function difference(HashSet $other): self
    {
        assert(Validate::areSame($this->itemType, $other->itemType));

        $difference = self::of($this->itemType);

        if ($this === $other) {
            return $difference;
        }

        $this->reject([$other, 'contains'])->each([$difference, 'add']);
        $other->reject([$this, 'contains'])->each([$difference, 'add']);

        return $difference;
    }

    /**
     * Retrieves the intersection.
     *
     * Creates a new set that contains items that are found in both the current
     * set and the provided set.
     *
     * A ∩ B = {x : x ∈ A ∧ x ∈ B}
     *
     * @phpstan-assert HashSet<T> $other
     */
    public function intersection(HashSet $other): self
    {
        assert(Validate::areSame($this->itemType, $other->itemType));

        $intersection = self::of($this->itemType);

        $this->filter([$other, 'contains'])->each([$intersection, 'add']);

        return $intersection;
    }

    /**
     * Retrieves the relative complement.
     *
     * Creates a new set that contains items in the provided set that are not
     * found in the current set.
     *
     * B \ A = {x : x ∈ B ∧ x ∉ A}
     *
     * @phpstan-assert HashSet<T> $other
     */
    public function complement(HashSet $other): self
    {
        assert(Validate::areSame($this->itemType, $other->itemType));

        $complement = self::of($this->itemType);

        if ($this === $other) {
            return $complement;
        }

        $other->reject([$this, 'contains'])->each([$complement, 'add']);

        return $complement;
    }

    /**
     * Retrieves the union.
     *
     * Creates a new set that contains items found in either the current set or
     * the provided set.
     *
     * A ∪ B = {x : x ∈ A ∨ x ∈ B}
     *
     * @phpstan-assert HashSet<T> $other
     */
    public function union(HashSet $other): self
    {
        assert(Validate::areSame($this->itemType, $other->itemType));

        $union = self::of($this->itemType);

        $this->each([$union, 'add']);
        $other->each([$union, 'add']);

        return $union;
    }

    /**
     * Creates a collection from the results of a function.
     *
     * @template U
     *
     * @param callable(T, int): U         $callback
     * @param class-string<U>|string|null $itemType
     *
     * @return HashSet<U>|HashSet
     */
    public function map(callable $callback, ?string $itemType = null): self
    {
        $set = self::of($itemType);

        foreach ($this->getIterator() as $index => $item) {
            $set->add(call_user_func($callback, $item, $index));
        }

        return $set;
    }

    /**
     * Creates a collection from items that pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return HashSet<T>
     */
    public function filter(callable $predicate): self
    {
        $set = self::of($this->itemType);

        foreach ($this->getIterator() as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $set->add($item);
            }
        }

        return $set;
    }

    /**
     * Creates a collection from items that fail a truth test.
     *
     * @param callable(T, int): bool $predicate
     *
     * @return HashSet<T>
     */
    public function reject(callable $predicate): self
    {
        $set = self::of($this->itemType);

        foreach ($this->getIterator() as $index => $item) {
            if (!call_user_func($predicate, $item, $index)) {
                $set->add($item);
            }
        }

        return $set;
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
     * @return HashSet<T>[]
     */
    public function partition(callable $predicate): array
    {
        $set1 = self::of($this->itemType);
        $set2 = self::of($this->itemType);

        foreach ($this->getIterator() as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                $set1->add($item);
            } else {
                $set2->add($item);
            }
        }

        return [$set1, $set2];
    }

    /**
     * Applies a callback function to every item.
     *
     * @param callable(T, int): void $callback
     */
    public function each(callable $callback): void
    {
        foreach ($this->getIterator() as $index => $item) {
            call_user_func($callback, $item, $index);
        }
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

            foreach ($this->getIterator() as $index => $item) {
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

            foreach ($this->getIterator() as $index => $item) {
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

        foreach ($this->getIterator() as $index => $item) {
            $accumulator = call_user_func($callback, $accumulator, $item, $index);
        }

        return $accumulator;
    }

    /**
     * Retrieves the sum of the collection
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
        foreach ($this->getIterator() as $index => $item) {
            if (call_user_func($predicate, $item, $index)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Checks if any items pass a truth test.
     *
     * @param callable(T, int): bool $predicate
     */
    public function any(callable $predicate): bool
    {
        foreach ($this->getIterator() as $index => $item) {
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
        foreach ($this->getIterator() as $index => $item) {
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
            $items[] = $item;
        }

        return $items;
    }

    /**
     * @inheritDoc
     *
     * @return Traversable<T>|Traversable
     */
    public function getIterator(): Traversable
    {
        return new GeneratorIterator(function (array $buckets) {
            $index = 0;
            /** @var SetBucketChain<T> $chain */
            foreach ($buckets as $chain) {
                for ($chain->rewind(); $chain->valid(); $chain->next()) {
                    yield $index => $chain->current();
                    $index++;
                }
            }
        }, [$this->buckets]);
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
        $buckets = [];

        foreach ($this->buckets as $hash => $chain) {
            $buckets[$hash] = clone $chain;
        }

        $this->buckets = $buckets;
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
