<?php

declare(strict_types=1);

namespace Liberty\System\Collection\Chain;

use Countable;
use Liberty\System\Collection\Chain\Bucket\Bucket;
use Liberty\System\Collection\Chain\Bucket\KeyValueBucket;
use Liberty\System\Collection\Chain\Bucket\TerminalBucket;
use Liberty\System\Exception\SystemException;
use Liberty\System\Utility\Validate;
use Liberty\System\Utility\VarPrinter;

/**
 * Class TableBucketChain
 *
 * @template K of mixed
 * @template V of mixed
 */
final class TableBucketChain implements Countable
{
    private TerminalBucket $head;
    private TerminalBucket $tail;
    private Bucket $current;
    private int $count = 0;
    private int $offset = -1;

    /**
     * Constructs TableBucketChain
     */
    public function __construct()
    {
        $this->head = new TerminalBucket();
        $this->tail = new TerminalBucket();
        $this->head->setNext($this->tail);
        $this->tail->setPrev($this->head);
        $this->current = $this->head;
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
     * Returns true if pair added; false if replaced.
     *
     * @param K $key
     * @param V $value
     */
    public function set(mixed $key, mixed $value): bool
    {
        $added = true;
        $bucket = $this->locate($key);

        if ($bucket !== null) {
            $this->removeBucket($bucket);
            $this->rewind();
            $added = false;
        }

        $this->insertBetween($key, $value, $this->head, $this->head->next());
        $this->offset = 0;

        return $added;
    }

    /**
     * Retrieves a value by key.
     *
     * @param K $key
     *
     * @throws SystemException When the key is not found
     */
    public function get(mixed $key): mixed
    {
        $bucket = $this->locate($key);

        if ($bucket === null) {
            $message = sprintf('Key not found: %s', VarPrinter::toString($key));
            throw new SystemException($message);
        }

        return $bucket->value();
    }

    /**
     * Checks if a key is defined.
     *
     * @param K $key
     */
    public function has(mixed $key): bool
    {
        return $this->locate($key) !== null;
    }

    /**
     * Removes a key-value pair by key.
     *
     * Returns true if pair removed; false otherwise.
     *
     * @param K $key
     */
    public function remove(mixed $key): bool
    {
        $removed = false;
        $bucket = $this->locate($key);

        if ($bucket !== null) {
            $this->removeBucket($bucket);
            $this->rewind();
            $removed = true;
        }

        return $removed;
    }

    /**
     * Sets the pointer to the first bucket.
     */
    public function rewind(): void
    {
        $this->current = $this->head->next();
        $this->offset = 0;
    }

    /**
     * Sets the pointer to the last bucket.
     */
    public function end(): void
    {
        $this->current = $this->tail->prev();
        $this->offset = $this->count - 1;
    }

    /**
     * Checks if the pointer is at a valid offset.
     */
    public function valid(): bool
    {
        return !($this->current instanceof TerminalBucket);
    }

    /**
     * Moves the pointer to the next bucket.
     */
    public function next(): void
    {
        if ($this->current instanceof TerminalBucket) {
            return;
        }

        $this->current = $this->current->next();
        $this->offset++;
    }

    /**
     * Moves the pointer to the previous bucket.
     */
    public function prev(): void
    {
        if ($this->current instanceof TerminalBucket) {
            return;
        }

        $this->current = $this->current->prev();
        $this->offset--;
    }

    /**
     * Retrieves the key from the current bucket.
     *
     * Returns null if the pointer is not at a valid offset.
     *
     * @return K|null
     */
    public function key(): mixed
    {
        if ($this->current instanceof TerminalBucket) {
            return null;
        }

        /** @var KeyValueBucket $current */
        $current = $this->current;

        return $current->key();
    }

    /**
     * Retrieves the value from the current bucket.
     *
     * Returns null if the pointer is not at a valid offset.
     *
     * @return V|null
     */
    public function current(): mixed
    {
        if ($this->current instanceof TerminalBucket) {
            return null;
        }

        /** @var KeyValueBucket $current */
        $current = $this->current;

        return $current->value();
    }

    /**
     * Handles deep cloning.
     */
    public function __clone(): void
    {
        $keys = [];
        $values = [];
        for ($this->rewind(); $this->valid(); $this->next()) {
            $values[] = $this->current();
            $keys[] = $this->key();
        }
        $this->head = new TerminalBucket();
        $this->tail = new TerminalBucket();
        $this->head->setNext($this->tail);
        $this->tail->setPrev($this->head);
        $this->current = $this->head;
        $this->count = 0;
        $this->offset = -1;
        $prev = $this->head;
        $next = $this->tail;
        $count = count($keys);
        for ($i = 0; $i < $count; $i++) {
            $this->insertBetween($keys[$i], $values[$i], $prev, $next);
            $prev = $this->current;
        }
    }

    /**
     * Locates a bucket by key.
     *
     * Returns null if the key is not found.
     *
     * @param K $key
     */
    private function locate(mixed $key): ?KeyValueBucket
    {
        for ($this->rewind(); $this->valid(); $this->next()) {
            /** @var KeyValueBucket $current */
            $current = $this->current;
            if (Validate::areEqual($key, $current->key())) {
                return $current;
            }
        }

        return null;
    }

    /**
     * Removes a bucket.
     */
    private function removeBucket(Bucket $bucket): void
    {
        $next = $bucket->next();
        $prev = $bucket->prev();

        $next->setPrev($prev);
        $prev->setNext($next);

        $this->count--;
    }

    /**
     * Inserts a key-value pair between two nodes.
     *
     * @param K $key
     * @param V $value
     */
    private function insertBetween(mixed $key, mixed $value, Bucket $prev, Bucket $next): void
    {
        $bucket = new KeyValueBucket($key, $value);

        $prev->setNext($bucket);
        $next->setPrev($bucket);

        $bucket->setPrev($prev);
        $bucket->setNext($next);

        $this->current = $bucket;
        $this->count++;
    }
}
