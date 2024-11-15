<?php

declare(strict_types=1);

namespace Liberty\System\Type;

use JsonSerializable;
use Liberty\System\Utility\ClassName;
use Liberty\System\Utility\Validate;

/**
 * Class Type
 */
final readonly class Type implements Equatable, JsonSerializable
{
    /**
     * Constructs Type.
     *
     * @internal
     */
    private function __construct(private string $name)
    {
    }

    /**
     * Creates instance from an object or class name.
     */
    public static function of(object|string $object): Type
    {
        return new self(ClassName::canonical($object));
    }

    /**
     * Retrieves the short class name.
     */
    public function toShortName(): string
    {
        return ClassName::short($this->name);
    }

    /**
     * Retrieves the full class name.
     */
    public function toClassName(): string
    {
        return ClassName::full($this->name);
    }

    /**
     * Retrieves lower-case underscored representation.
     */
    public function toUnderscored(): string
    {
        return ClassName::underscore($this->name);
    }

    /**
     * Retrieves the canonical representation.
     */
    public function toCanonical(): string
    {
        return $this->name;
    }

    /**
     * Retrieves a string representation.
     */
    public function toString(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function equals(mixed $object): bool
    {
        if ($this === $object) {
            return true;
        }

        if (!Validate::areSameType($this, $object)) {
            return false;
        }

        return $this->name === $object->name;
    }

    /**
     * @inheritDoc
     */
    public function hashValue(): string
    {
        return $this->name;
    }

    /**
     * Retrieves a string representation.
     */
    public function jsonSerialize(): string
    {
        return $this->name;
    }

    /**
     * Handles string casting.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
