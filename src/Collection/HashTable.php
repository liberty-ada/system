<?php

declare(strict_types=1);

namespace Liberty\System\Collection;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Liberty\System\Collection\Chain\TableBucketChain;
use Liberty\System\Collection\Iterator\GeneratorIterator;
use Liberty\System\Exception\SystemException;
use Liberty\System\Utility\FastHasher;
use Liberty\System\Utility\Validate;
use Liberty\System\Utility\VarPrinter;
use Traversable;

/**
 * Class HashTable
 *
 * @template K of mixed
 * @template V of mixed
 */
final class HashTable implements ArrayAccess, Countable, IteratorAggregate
{
    private ?string $keyType;
    private ?string $valueType;
    /** @var array<string, TableBucketChain> */
    private array $buckets = [];
    private int $count = 0;

    /**
     * Constructs HashTable
     *
     * If types are not provided, the types are dynamic.
     *
     * The type can be any fully-qualified class or interface name,
     * or one of the following type strings:
     * [array, object, bool, int, float, string, callable]
     */
    public function __construct(?string $keyType = null, ?string $valueType = null)
    {
        $this->setKeyType($keyType);
        $this->setValueType($valueType);
    }

    /**
     * Creates HashTable instance.
     *
     * @return HashTable<K, V>|HashTable
     */
    public static function of(?string $keyType = null, ?string $valueType = null): self
    {
        return new self($keyType, $valueType);
    }

    /**
     * Retrieves the key type.
     *
     * Returns null if the key type is dynamic.
     */
    public function keyType(): ?string
    {
        return $this->keyType;
    }

    /**
     * Retrieves the value type.
     *
     * Returns null if the value type is dynamic.
     */
    public function valueType(): ?string
    {
        return $this->valueType;
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
     * Sets a key-value pair.
     *
     * @param K $key
     * @param V $value
     *
     * @phpstan-assert K $key
     * @phpstan-assert V $value
     */
    public function set(mixed $key, mixed $value): void
    {
        assert(Validate::isType($key, $this->keyType()));
        assert(Validate::isType($value, $this->valueType()));

        $hash = FastHasher::hash($key);

        if (!isset($this->buckets[$hash])) {
            $this->buckets[$hash] = new TableBucketChain();
        }

        if ($this->buckets[$hash]->set($key, $value)) {
            $this->count++;
        }
    }

    /**
     * Retrieves a value by key.
     *
     * @param K $key
     *
     * @return V
     *
     * @throws SystemException When the key is not defined
     */
    public function get(mixed $key): mixed
    {
        $hash = FastHasher::hash($key);

        if (!isset($this->buckets[$hash])) {
            $message = sprintf('Key not found: %s', VarPrinter::toString($key));
            throw new SystemException($message);
        }

        return $this->buckets[$hash]->get($key);
    }

    /**
     * Checks if a key is defined.
     *
     * @param K $key
     */
    public function has(mixed $key): bool
    {
        $hash = FastHasher::hash($key);

        if (!isset($this->buckets[$hash])) {
            return false;
        }

        return $this->buckets[$hash]->has($key);
    }

    /**
     * Removes a value by key.
     *
     * @param K $key
     */
    public function remove(mixed $key): void
    {
        $hash = FastHasher::hash($key);

        if (isset($this->buckets[$hash])) {
            if ($this->buckets[$hash]->remove($key)) {
                $this->count--;
                if ($this->buckets[$hash]->isEmpty()) {
                    unset($this->buckets[$hash]);
                }
            }
        }
    }

    /**
     * Sets a key-value pair.
     *
     * @param K          $offset
     * @param V          $value
     *
     * @phpstan-assert K $offset
     * @phpstan-assert V $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Retrieves a value by key.
     *
     * @param K $offset
     *
     * @return V
     *
     * @throws SystemException When the key is not defined
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Checks if a key is defined.
     *
     * @param K $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Removes a value by key.
     *
     * @param K $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * Retrieves an iterator for keys
     */
    public function keys(): iterable
    {
        return new GeneratorIterator(function (array $buckets) {
            /** @var TableBucketChain $chain */
            foreach ($buckets as $chain) {
                for ($chain->rewind(); $chain->valid(); $chain->next()) {
                    yield $chain->key();
                }
            }
        }, [$this->buckets]);
    }

    /**
     * Creates a collection from the results of a function.
     *
     * Keys are not affected.
     *
     * @template U
     *
     * @param callable(V, K): U           $callback
     * @param class-string<U>|string|null $valueType
     *
     * @return HashTable<K, U>|HashTable
     */
    public function map(callable $callback, ?string $valueType = null): self
    {
        $table = self::of($this->keyType(), $valueType);

        foreach ($this->getIterator() as $key => $value) {
            $table->set($key, call_user_func($callback, $value, $key));
        }

        return $table;
    }

    /**
     * Creates a collection from values that pass a truth test.
     *
     * @param callable(V, K): bool $predicate
     *
     * @return HashTable<K, V>|HashTable
     */
    public function filter(callable $predicate): self
    {
        $table = self::of($this->keyType(), $this->valueType());

        foreach ($this->getIterator() as $key => $value) {
            if (call_user_func($predicate, $value, $key)) {
                $table->set($key, $value);
            }
        }

        return $table;
    }

    /**
     * Creates a collection from values that fail a truth test.
     *
     * @param callable(V, K): bool $predicate
     *
     * @return HashTable<K, V>|HashTable
     */
    public function reject(callable $predicate): self
    {
        $table = self::of($this->keyType(), $this->valueType());

        foreach ($this->getIterator() as $key => $value) {
            if (!call_user_func($predicate, $value, $key)) {
                $table->set($key, $value);
            }
        }

        return $table;
    }

    /**
     * Creates two collections based on a truth test.
     *
     * Values that pass the truth test are placed in the first collection.
     *
     * Values that fail the truth test are placed in the second collection.
     *
     * @param callable(V, K): bool $predicate
     *
     * @return HashTable<K, V>[]|HashTable[]
     */
    public function partition(callable $predicate): array
    {
        $table1 = self::of($this->keyType(), $this->valueType());
        $table2 = self::of($this->keyType(), $this->valueType());

        foreach ($this->getIterator() as $key => $value) {
            if (call_user_func($predicate, $value, $key)) {
                $table1->set($key, $value);
            } else {
                $table2->set($key, $value);
            }
        }

        return [$table1, $table2];
    }

    /**
     * Applies a callback function to every value.
     *
     * @param callable(V, K): void $callback
     */
    public function each(callable $callback): void
    {
        foreach ($this->getIterator() as $key => $value) {
            call_user_func($callback, $value, $key);
        }
    }

    /**
     * Retrieves the first key for a value that passes a truth test.
     *
     * Returns null if no key-value pair passes the test.
     *
     * @param callable(V, K): bool $predicate
     *
     * @return K|null
     */
    public function find(callable $predicate): mixed
    {
        foreach ($this->getIterator() as $key => $value) {
            if (call_user_func($predicate, $value, $key)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Retrieves the iterator.
     *
     * @return Traversable<K, V>|Traversable
     */
    public function getIterator(): Traversable
    {
        return new GeneratorIterator(function (HashTable $table) {
            foreach ($table->keys() as $key) {
                yield $key => $table->get($key);
            }
        }, [$this]);
    }

    /**
     * Handles deep cloning
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
     * Sets the key type.
     *
     * If a type is not provided, the key type is dynamic.
     *
     * The type can be any fully-qualified class or interface name,
     * or one of the following type strings:
     * [array, object, bool, int, float, string, callable]
     */
    private function setKeyType(?string $keyType = null): void
    {
        $this->keyType = $keyType;
    }

    /**
     * Sets the value type.
     *
     * If a type is not provided, the value type is dynamic.
     *
     * The type can be any fully-qualified class or interface name,
     * or one of the following type strings:
     * [array, object, bool, int, float, string, callable]
     */
    private function setValueType(?string $valueType = null): void
    {
        $this->valueType = $valueType;
    }
}
