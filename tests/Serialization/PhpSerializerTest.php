<?php

declare(strict_types=1);

namespace Liberty\System\Test\Serialization;

use Liberty\System\Exception\DomainException;
use Liberty\System\Serialization\PhpSerializer;
use Liberty\System\Test\TestCase\UnitTestCase;
use Liberty\System\Type\Type;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PhpSerializer::class)]
class PhpSerializerTest extends UnitTestCase
{
    public function test_that_serialize_returns_expected_state(): void
    {
        $state = 'a:2:{s:1:"@";s:24:"Liberty.System.Type.Type";s:1:"$";a:1:'
            .'{s:4:"name";s:42:"Liberty.System.Serialization.PhpSerializer";}}';
        $phpSerializer = new PhpSerializer();
        $type = Type::of($phpSerializer);

        self::assertSame($state, $phpSerializer->serialize($type));
    }

    public function test_that_deserialize_returns_expected_instance(): void
    {
        $state = 'a:2:{s:1:"@";s:24:"Liberty.System.Type.Type";s:1:"$";a:1:'
            .'{s:4:"name";s:42:"Liberty.System.Serialization.PhpSerializer";}}';
        $phpSerializer = new PhpSerializer();

        /** @var Type $type */
        $type = $phpSerializer->deserialize($state);

        self::assertSame('PhpSerializer', $type->toShortName());
    }

    public function test_that_deserialize_throws_exception_with_invalid_state(): void
    {
        self::expectException(DomainException::class);

        $state = 'a:2:{s:3:"foo";s:3:"bar";s:3:"baz";s:4:"buzz";}';
        $phpSerializer = new PhpSerializer();

        $phpSerializer->deserialize($state);
    }
}
