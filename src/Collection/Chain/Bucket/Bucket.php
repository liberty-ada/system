<?php

declare(strict_types=1);

namespace Liberty\System\Collection\Chain\Bucket;

/**
 * Interface Bucket
 */
interface Bucket
{
    /**
     * Sets the next bucket.
     */
    public function setNext(?Bucket $next): void;

    /**
     * Retrieves the next bucket.
     */
    public function next(): ?Bucket;

    /**
     * Sets the previous bucket.
     */
    public function setPrev(?Bucket $prev): void;

    /**
     * Retrieves the previous bucket.
     */
    public function prev(): ?Bucket;
}
