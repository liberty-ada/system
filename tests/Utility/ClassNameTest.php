<?php

declare(strict_types=1);

namespace Liberty\System\Test\Utility;

use ArrayObject;
use Liberty\System\Test\TestCase\UnitTestCase;
use Liberty\System\Utility\ClassName;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ClassName::class)]
class ClassNameTest extends UnitTestCase
{
    public function test_that_full_returns_expected_value_from_class_name(): void
    {
        $className = ClassName::class;
        $expected = 'Liberty\\System\\Utility\\ClassName';

        self::assertSame($expected, ClassName::full($className));
    }

    public function test_that_full_returns_expected_value_from_object(): void
    {
        $object = new ArrayObject([]);
        $expected = 'ArrayObject';

        self::assertSame($expected, ClassName::full($object));
    }

    public function test_that_canonical_returns_expected_value(): void
    {
        $className = ClassName::class;
        $expected = 'Liberty.System.Utility.ClassName';

        self::assertSame($expected, ClassName::canonical($className));
    }

    public function test_that_underscore_returns_expected_value(): void
    {
        $className = ClassName::class;
        $expected = 'liberty.system.utility.class_name';

        self::assertSame($expected, ClassName::underscore($className));
    }

    public function test_that_short_returns_expected_value(): void
    {
        $className = ClassName::class;
        $expected = 'ClassName';

        self::assertSame($expected, ClassName::short($className));
    }
}
