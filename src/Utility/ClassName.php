<?php

declare(strict_types=1);

namespace Liberty\System\Utility;

/**
 * Class ClassName
 */
final class ClassName
{
    /**
     * Retrieves the fully qualified class name of an object.
     *
     * @template T of object
     *
     * @param object<T>|class-string<T> $object
     *
     * @return class-string<T>
     */
    public static function full(object|string $object): string
    {
        if (is_string($object)) {
            return str_replace('.', '\\', $object);
        }

        return trim($object::class, '\\');
    }

    /**
     * Retrieves the canonical class name of an object.
     *
     * @template T of object
     *
     * @param object<T>|class-string<T> $object
     */
    public static function canonical(object|string $object): string
    {
        return str_replace('\\', '.', self::full($object));
    }

    /**
     * Retrieves the lowercase underscored class name of an object.
     *
     * @template T of object
     *
     * @param object<T>|class-string<T> $object
     */
    public static function underscore(object|string $object): string
    {
        return strtolower(
            preg_replace(
                '/(?<=\\w)([A-Z])/',
                '_$1',
                self::canonical($object)
            )
        );
    }

    /**
     * Retrieves the short class name of an object.
     *
     * @template T of object
     *
     * @param object<T>|class-string<T> $object
     */
    public static function short(object|string $object): string
    {
        $parts = explode('\\', self::full($object));

        return end($parts);
    }
}
