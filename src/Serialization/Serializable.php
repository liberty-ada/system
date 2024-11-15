<?php

declare(strict_types=1);

namespace Liberty\System\Serialization;

use Liberty\System\Exception\DomainException;

/**
 * Interface Serializable
 */
interface Serializable
{
    /**
     * Creates instance from a serialized representation
     *
     * @return static
     *
     * @throws DomainException When the data is not valid
     */
    public static function arrayDeserialize(array $data);

    /**
     * Retrieves a serialized representation
     */
    public function arraySerialize(): array;
}
