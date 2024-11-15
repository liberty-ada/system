<?php

declare(strict_types=1);

namespace Liberty\System\Collection\Chain\Bucket;

/**
 * Class TerminalBucket
 */
final class TerminalBucket implements Bucket
{
    private ?Bucket $next = null;
    private ?Bucket $prev = null;

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
}
