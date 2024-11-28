<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection\Iterator;

use Exception;
use Liberty\System\Collection\Iterator\GeneratorIterator;
use Liberty\System\Exception\DomainException;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GeneratorIterator::class)]
class GeneratorIteratorTest extends UnitTestCase
{
    public function test_that_rewind_allows_iteration_more_than_once(): void
    {
        $iterator = new GeneratorIterator(function () {
            for ($i = 0; $i < 10; $i++) {
                yield $i => $i;
            }
        });

        $count = 0;
        foreach ($iterator as $key => $value) {
            $count++;
        }

        self::assertFalse($iterator->valid());

        foreach ($iterator as $key => $value) {
            $count++;
        }
    }

    public function test_that_valid_returns_true_with_valid_position(): void
    {
        $iterator = new GeneratorIterator(function () {
            for ($i = 0; $i < 10; $i++) {
                yield $i => $i;
            }
        });

        self::assertTrue($iterator->valid());
    }

    public function test_that_current_returns_first_yielded_value(): void
    {
        $iterator = new GeneratorIterator(function () {
            for ($i = 0; $i < 10; $i++) {
                yield $i => $i;
            }
        });

        self::assertSame(0, $iterator->current());
    }

    public function test_that_key_returns_first_yielded_key(): void
    {
        $iterator = new GeneratorIterator(function () {
            for ($i = 0; $i < 10; $i++) {
                yield $i => $i;
            }
        });

        self::assertSame(0, $iterator->key());
    }

    public function test_that_next_advances_to_next_position(): void
    {
        $iterator = new GeneratorIterator(function () {
            for ($i = 0; $i < 10; $i++) {
                yield $i => $i;
            }
        });

        $iterator->next();

        self::assertSame(1, $iterator->key());
    }

    public function test_that_send_injects_value_to_generator(): void
    {
        $iterator = new GeneratorIterator(function () {
            $buffer = '';
            while (true) {
                $buffer .= (yield $buffer);
            }
        });

        $iterator->send('Hello');
        $iterator->send(' ');
        $iterator->send('World');

        self::assertSame('Hello World', $iterator->current());
    }

    public function test_that_throw_sends_an_exception_into_generator(): void
    {
        $iterator = new GeneratorIterator(function () {
            $buffer = '';
            while (true) {
                try {
                    $buffer .= (yield $buffer);
                } catch (Exception $e) {
                    $buffer .= $e->getMessage();
                }
            }
        });

        $iterator->throw(new Exception('Oops!'));
        $iterator->send(' ');
        $iterator->send('Hello');
        $iterator->send(' ');
        $iterator->send('World');

        self::assertSame('Oops! Hello World', $iterator->current());
    }

    public function test_that_get_return_returns_expected_value(): void
    {
        $return = 'foo';
        $iterator = new GeneratorIterator(function () use ($return) {
            for ($i = 0; $i < 10; $i++) {
                yield $i => $i;
            }

            return $return;
        });

        foreach ($iterator as $index => $value) {
            //
        }

        $this->assertSame($return, $iterator->getReturn());
    }

    public function test_that_get_return_throws_exception_when_has_not_returned(): void
    {
        self::expectException(Exception::class);

        $return = 'foo';
        $iterator = new GeneratorIterator(function () use ($return) {
            for ($i = 0; $i < 10; $i++) {
                yield $i => $i;
            }

            return $return;
        });

        $this->assertSame($return, $iterator->getReturn());
    }

    public function test_that_constructor_throws_exception_when_function_is_not_generator(): void
    {
        self::expectException(DomainException::class);

        new GeneratorIterator(function () { });
    }
}
