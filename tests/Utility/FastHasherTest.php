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

    public function test_that_hash_returns_expected_value_for_null()
    {
        $expected = '350ca8af';

        self::assertSame($expected, FastHasher::hash(null, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_true()
    {
        $expected = 'ba3db26d';

        self::assertSame($expected, FastHasher::hash(true, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_false()
    {
        $expected = 'b93db0da';

        self::assertSame($expected, FastHasher::hash(false, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_std_class()
    {
        $object = new \StdClass();
        $objHash = spl_object_hash($object);
        $expected = hash(self::HASH_ALGO, sprintf('o_%s', $objHash));

        self::assertSame($expected, FastHasher::hash($object, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_equatable()
    {
        $type = Type::of(FastHasher::class);
        $expected = hash(self::HASH_ALGO, sprintf('e_%s', $type));

        self::assertSame($expected, FastHasher::hash($type, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_string()
    {
        $expected = '8dffd835';

        self::assertSame($expected, FastHasher::hash('Hello World', self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_integer()
    {
        $expected = '0ad1d667';

        self::assertSame($expected, FastHasher::hash(42, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_float()
    {
        $expected = '82fbd3a2';

        self::assertSame($expected, FastHasher::hash(3.14, self::HASH_ALGO));
    }

    public function test_that_hash_returns_expected_value_for_resource()
    {
        $handle = fopen(__FILE__, 'r');
        $expected = hash(self::HASH_ALGO, sprintf('r_%d', (int) $handle));

        self::assertSame($expected, FastHasher::hash($handle, self::HASH_ALGO));

        fclose($handle);
    }

    public function test_that_hash_returns_expected_value_for_array()
    {
        $expected = '9568b1b3';

        self::assertSame($expected, FastHasher::hash(['foo', 'bar', 'baz'], self::HASH_ALGO));
    }
}
