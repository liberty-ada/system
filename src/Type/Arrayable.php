<?php

declare(strict_types=1);

namespace Liberty\System\Type;

/**
 * Interface Arrayable
 */
interface Arrayable
{
    /**
     * Retrieves an array representation.
     */
    public function toArray(): array;
}
