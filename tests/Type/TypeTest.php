<?php

declare(strict_types=1);

namespace Liberty\System\Test\Type;

use Liberty\System\Exception\DomainException;
use Liberty\System\Serialization\JsonSerializer;
use Liberty\System\Test\TestCase\UnitTestCase;
use Liberty\System\Type\Type;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Type::class)]
class TypeTest extends UnitTestCase
{
    public function test_that_to_short_name_returns_expected_value(): void
    {
        $shortName = 'Type';
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertSame($shortName, $type->toShortName());
    }

    public function test_that_to_class_name_returns_expected_value(): void
    {
        $className = 'Liberty\\System\\Type\\Type';
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertSame($className, $type->toClassName());
    }

    public function test_that_to_underscored_returns_expected_value(): void
    {
        $underscored = 'liberty.system.type.type';
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertSame($underscored, $type->toUnderscored());
    }

    public function test_that_to_canonical_returns_expected_value(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertSame($canonical, $type->toCanonical());
    }

    public function test_that_to_string_returns_expected_value(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertSame($canonical, $type->toString());
    }

    public function test_that_string_cast_returns_expected_value(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertSame($canonical, (string) $type);
    }

    public function test_that_it_is_json_encodable(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);
        $data = ['type' => $type];

        self::assertSame('{"type":"Liberty.System.Type.Type"}', json_encode($data));
    }

    public function test_that_it_is_serializable(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertTrue(unserialize(serialize($type))->equals($type));
    }

    public function test_that_equals_returns_true_when_same_instance(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertTrue($type->equals($type));
    }

    public function test_that_equals_returns_false_when_different_types(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertFalse($type->equals($canonical));
    }

    public function test_that_equals_returns_true_when_equal(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type1 = Type::of($canonical);
        $type2 = Type::of($canonical);

        self::assertTrue($type1->equals($type2));
    }

    public function test_that_hash_value_returns_expected_value(): void
    {
        $canonical = 'Liberty.System.Type.Type';

        $type = Type::of($canonical);

        self::assertSame($canonical, $type->hashValue());
    }

    public function test_that_it_can_be_serialized(): void
    {
        $type = Type::of(JsonSerializer::class);
        $serializer = new JsonSerializer();
        $state = $serializer->serialize($type);
        /** @var Type $newType */
        $newType = $serializer->deserialize($state);

        self::assertSame($type->toString(), $newType->toString());
    }

    public function test_that_array_deserialize_throws_exception_for_invalid_data(): void
    {
        self::expectException(DomainException::class);

        $state = '{"@":"Liberty.System.Type.Type","$":["Liberty.System.Serialization.JsonSerializer"]}';
        $serializer = new JsonSerializer();

        $serializer->deserialize($state);
    }
}
