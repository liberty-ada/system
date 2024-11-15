<?php

declare(strict_types=1);

namespace Liberty\System\Collection\Chain\Bucket;

/**
 * Class KeyValueBucket
 *
 * @template K of mixed
 * @template V of mixed
 */
final class KeyValueBucket implements Bucket
{
    private ?Bucket $next = null;
    private ?Bucket $prev = null;

    /**
     * Constructs KeyValueBucket
     *
     * @param K $key
     * @param V $value
     */
    public function __construct(
        private readonly mixed $key,
        private readonly mixed $value
    ) {
    }

    /**
     * @inheritDoc
     */
    public function setNext(?Bucket $next): void
    {
        $this->next = $next;
    }

    /**
     * @inheritDoc
     */
    public function next(): ?Bucket
    {
        return $this->next;
    }

    /**
     * @inheritDoc
     */
    public function setPrev(?Bucket $prev): void
    {
        $this->prev = $prev;
    }

    /**
     * @inheritDoc
     */
    public function prev(): ?Bucket
    {
        return $this->prev;
    }

    /**
     * Retrieves the key
     *
     * @return K
     */
    public function key(): mixed
    {
        return $this->key;
    }

    /**
     * Retrieves the value
     *
     * @return V
     */
    public function value(): mixed
    {
        return $this->value;
    }
}
