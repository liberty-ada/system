<?php

declare(strict_types=1);

namespace Liberty\System\Test\Serialization;

use Liberty\System\Exception\DomainException;
use Liberty\System\Serialization\JsonSerializer;
use Liberty\System\Test\TestCase\UnitTestCase;
use Liberty\System\Type\Type;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JsonSerializer::class)]
class JsonSerializerTest extends UnitTestCase
{
    public function test_that_serialize_returns_expected_state(): void
    {
        $state = '{"@":"Liberty.System.Type.Type","$":{"name":"Liberty.System.Serialization.JsonSerializer"}}';
        $jsonSerializer = new JsonSerializer();
        $type = Type::of($jsonSerializer);

        self::assertSame($state, $jsonSerializer->serialize($type));
    }

    public function test_that_deserialize_returns_expected_instance(): void
    {
        $state = '{"@":"Liberty.System.Type.Type","$":{"name":"Liberty.System.Serialization.JsonSerializer"}}';
        $jsonSerializer = new JsonSerializer();

        /** @var Type $type */
        $type = $jsonSerializer->deserialize($state);

        self::assertSame('JsonSerializer', $type->toShortName());
    }

    public function test_that_deserialize_throws_exception_with_invalid_state(): void
    {
        self::expectException(DomainException::class);

        $state = 'Liberty.System.Serialization.JsonSerializer';
        $jsonSerializer = new JsonSerializer();

        $jsonSerializer->deserialize($state);
    }
}
