<?php

declare(strict_types=1);

namespace Liberty\System\Type;

/**
 * Interface Equatable
 */
interface Equatable
{
    /**
     * Checks if an object equals this instance.
     *
     * The passed object must be an instance of the same type.
     *
     * The method should return false for invalid object types, rather than
     * throw an exception.
     *
     * @template T of static
     *
     * @param T $object
     *
     * @return ($object is T ? bool : false)
     */
    public function equals(mixed $object): bool;

    /**
     * Retrieves a string representation for hashing.
     *
     * The returned value must behave in a way consistent with the same
     * object's equals() method.
     *
     * A given object must consistently report the same hash value (unless it
     * is changed so that the new version is no longer considered "equal" to
     * the old), and two objects which equals() says are equal must report the
     * same hash value.
     */
    public function hashValue(): string;
}
