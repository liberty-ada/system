<?php

declare(strict_types=1);

namespace Liberty\System\Serialization;

use Liberty\System\Exception\DomainException;
use Liberty\System\Utility\ClassName;

/**
 * Class PhpSerializer
 */
final class PhpSerializer implements Serializer
{
    /**
     * @inheritDoc
     */
    public function deserialize(string $state): Serializable
    {
        $data = unserialize($state);

        $keys = ['@', '$'];
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                $message = sprintf('Invalid serialization format: %s', $state);
                throw new DomainException($message);
            }
        }

        $class = ClassName::full($data['@']);

        /** @var Serializable|string $class */
        return $class::arrayDeserialize($data['$']);
    }

    /**
     * @inheritDoc
     */
    public function serialize(Serializable $object): string
    {
        $data = [
            '@' => ClassName::canonical($object),
            '$' => $object->arraySerialize()
        ];

        return serialize($data);
    }
}
