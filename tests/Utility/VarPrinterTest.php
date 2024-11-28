<?php

declare(strict_types=1);

namespace Liberty\System\Test\Utility;

use Liberty\System\Test\TestCase\UnitTestCase;
use Liberty\System\Type\Type;
use Liberty\System\Utility\VarPrinter;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VarPrinter::class)]
class VarPrinterTest extends UnitTestCase
{
    public function test_that_to_string_returns_expected_string_for_null(): void
    {
        $expected = 'NULL';

        self::assertSame($expected, VarPrinter::toString(null));
    }

    public function test_that_to_string_returns_expected_string_for_true(): void
    {
        $expected = 'TRUE';

        self::assertSame($expected, VarPrinter::toString(true));
    }

    public function test_that_to_string_returns_expected_string_for_false(): void
    {
        $expected = 'FALSE';

        self::assertSame($expected, VarPrinter::toString(false));
    }

    public function test_that_to_string_returns_expected_string_for_std_class(): void
    {
        $expected = 'Object(stdClass)';
        $object = new \StdClass();

        self::assertSame($expected, VarPrinter::toString($object));
    }

    public function test_that_to_string_returns_expected_string_for_anon_function(): void
    {
        $expected = 'Function';
        $function = function () { };

        self::assertSame($expected, VarPrinter::toString($function));
    }

    public function test_that_to_string_returns_expected_string_for_datetime(): void
    {
        $expected = 'DateTime(2015-01-01T00:00:00+00:00)';
        $dateTime = new \DateTime('2015-01-01', new \DateTimeZone('UTC'));

        self::assertSame($expected, VarPrinter::toString($dateTime));
    }

    public function test_that_to_string_returns_expected_string_for_cast_object(): void
    {
        $expected = __FILE__;
        $object = new \SplFileInfo(__FILE__);

        self::assertSame($expected, VarPrinter::toString($object));
    }

    public function test_that_to_string_returns_expected_string_for_object_to_string(): void
    {
        $expected = 'Liberty.System.Utility.VarPrinter';
        $type = Type::of(VarPrinter::class);

        self::assertSame($expected, VarPrinter::toString($type));
    }

    public function test_that_to_string_returns_expected_string_for_exception(): void
    {
        $line = __LINE__ + 1;
        $object = new \RuntimeException('Something went wrong', 31337);
        $expected = sprintf('RuntimeException(%s)', json_encode([
            'message' => 'Something went wrong',
            'code'    => 31337,
            'file'    => __FILE__,
            'line'    => $line
        ], JSON_UNESCAPED_SLASHES));

        self::assertSame($expected, VarPrinter::toString($object));
    }

    public function test_that_to_string_returns_expected_string_for_simple_array(): void
    {
        $expected = 'Array(0 => foo, 1 => bar, 2 => baz)';
        $data = ['foo', 'bar', 'baz'];

        self::assertSame($expected, VarPrinter::toString($data));
    }

    public function test_that_to_string_returns_expected_string_for_assoc_array(): void
    {
        $expected = 'Array(foo => bar)';
        $data = ['foo' => 'bar'];

        self::assertSame($expected, VarPrinter::toString($data));
    }

    public function test_that_to_string_returns_expected_string_for_resource(): void
    {
        $resource = fopen(__FILE__, 'r');
        $expected = sprintf('Resource(%d:stream)', get_resource_id($resource));

        self::assertSame($expected, VarPrinter::toString($resource));

        fclose($resource);
    }
}
