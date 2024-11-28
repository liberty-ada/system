<?php

declare(strict_types=1);

namespace Liberty\System\Test\Utility;

use Liberty\System\Test\TestCase\UnitTestCase;
use Liberty\System\Type\Type;
use Liberty\System\Utility\FastHasher;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FastHasher::class)]
class FastHasherTest extends UnitTestCase
{
    public const string HASH_ALGO = 'fnv1a32';

    public function test_that_hash_returns_expected_value_for_null(): void
    {
        $expected = '350ca8af';

        self::assertSame($expected, FastHasher::hash(null, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_true(): void
    {
        $expected = 'ba3db26d';

        self::assertSame($expected, FastHasher::hash(true, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_false(): void
    {
        $expected = 'b93db0da';

        self::assertSame($expected, FastHasher::hash(false, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_std_class(): void
    {
        $object = new \StdClass();
        $objHash = spl_object_hash($object);
        $expected = hash(self::HASH_ALGO, sprintf('o_%s', $objHash));

        self::assertSame($expected, FastHasher::hash($object, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_equatable(): void
    {
        $type = Type::of(FastHasher::class);
        $expected = hash(self::HASH_ALGO, sprintf('e_%s', $type));

        self::assertSame($expected, FastHasher::hash($type, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_string(): void
    {
        $expected = '8dffd835';

        self::assertSame($expected, FastHasher::hash('Hello World', self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_integer(): void
    {
        $expected = '0ad1d667';

        self::assertSame($expected, FastHasher::hash(42, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_float(): void
    {
        $expected = '82fbd3a2';

        self::assertSame($expected, FastHasher::hash(3.14, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_resource(): void
    {
        $handle = fopen(__FILE__, 'r');
        $expected = hash(self::HASH_ALGO, sprintf('r_%d', (int) $handle));

        self::assertSame($expected, FastHasher::hash($handle, self::HASH_ALGO));

        fclose($handle);
    }

    public function test_that_hash_returns_expected_value_for_array(): void
    {
        $expected = '9568b1b3';

        self::assertSame($expected, FastHasher::hash(['foo', 'bar', 'baz'], self::HASH_ALGO));
    }
}
