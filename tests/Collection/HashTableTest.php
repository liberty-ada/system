<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection;

use AssertionError;
use Liberty\System\Collection\HashTable;
use Liberty\System\Exception\SystemException;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HashTable::class)]
class HashTableTest extends UnitTestCase
{
    public function test_that_it_is_empty_by_default(): void
    {
        self::assertTrue(HashTable::of('string', 'string')->isEmpty());
    }

    public function test_that_duplicate_keys_do_not_affect_count(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable->set('foo', 'bar');
        $hashTable->set('baz', 'buz');
        $hashTable->set('foo', 'bar');

        self::assertSame(2, count($hashTable));
    }

    public function test_that_get_returns_expected_value_for_key(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable->set('foo', 'bar');
        $hashTable->set('baz', 'buz');

        self::assertSame('bar', $hashTable->get('foo'));
    }

    public function test_that_has_returns_true_when_key_is_in_the_table(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable->set('foo', 'bar');

        self::assertTrue($hashTable->has('foo'));
    }

    public function test_that_has_returns_false_when_key_is_not_in_the_table(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable->set('foo', 'bar');

        self::assertFalse($hashTable->has('baz'));
    }

    public function test_that_has_returns_false_after_key_is_removed(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable->set('foo', 'bar');
        $hashTable->remove('foo');

        self::assertFalse($hashTable->has('foo'));
    }

    public function test_that_offset_get_returns_expected_value_for_key(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        $hashTable['baz'] = 'buz';

        self::assertSame('bar', $hashTable['foo']);
    }

    public function test_that_offset_exists_returns_true_when_key_is_in_the_table(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';

        self::assertTrue(isset($hashTable['foo']));
    }

    public function test_that_offset_exists_returns_false_when_key_is_not_in_the_table(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';

        self::assertFalse(isset($hashTable['baz']));
    }

    public function test_that_offset_exists_returns_false_after_key_is_removed(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        unset($hashTable['foo']);

        self::assertFalse(isset($hashTable['foo']));
    }

    public function test_that_keys_returns_traversable_list_of_keys(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable->set('foo', 'bar');
        $hashTable->set('baz', 'buz');
        $keys = $hashTable->keys();

        $output = [];

        foreach ($keys as $key) {
            $output[] = $key;
        }

        self::assertContains('foo', $output);
    }

    public function test_that_it_is_traversable(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable->set('foo', 'bar');
        $hashTable->set('baz', 'buz');

        foreach ($hashTable as $key => $value) {
            if ($key === 'baz') {
                self::assertSame('buz', $value);
            }
        }
    }

    public function test_that_clone_include_nested_collection(): void
    {
        $hashTable = HashTable::of('int', 'int');
        $items = range(0, 9);

        foreach ($items as $i) {
            $hashTable->set($i, $i);
        }

        $copy = clone $hashTable;

        foreach ($items as $i) {
            $hashTable->remove($i);
        }

        $output = [];

        foreach ($copy->keys() as $key) {
            $output[] = $key;
        }

        self::assertSame($items, $output);
    }

    public function test_that_iterator_key_returns_null_when_invalid(): void
    {
        $hashTable = HashTable::of('string', 'string');

        self::assertNull($hashTable->getIterator()->key());
    }

    public function test_that_iterator_current_returns_null_when_invalid(): void
    {
        $hashTable = HashTable::of('string', 'string');

        self::assertNull($hashTable->getIterator()->current());
    }

    public function test_that_each_calls_callback_with_each_value(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        $hashTable['baz'] = 'buz';

        $output = HashTable::of('string', 'string');

        $hashTable->each(function ($value, $key) use ($output) {
            $output->set($key, $value);
        });

        $data = [];

        foreach ($output as $key => $value) {
            $data[$key] = $value;
        }

        self::assertCount(2, $data);
    }

    public function test_that_map_returns_expected_table(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        $hashTable['baz'] = 'buz';

        $output = $hashTable->map(function ($value, $key) {
            return strlen($value);
        }, 'int');

        $data = [];

        foreach ($output as $key => $value) {
            $data[$key] = $value;
        }

        self::assertSame(['foo' => 3, 'baz' => 3], $data);
    }

    public function test_that_find_returns_expected_key(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        $hashTable['baz'] = 'buz';

        $key = $hashTable->find(function ($value, $key) {
            return substr($key, 0, 1) === 'f';
        });

        self::assertSame('foo', $key);
    }

    public function test_that_find_returns_null_when_value_not_found(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        $hashTable['baz'] = 'buz';

        $key = $hashTable->find(function ($value, $key) {
            return substr($value, 0, 1) === 'c';
        });

        self::assertNull($key);
    }

    public function test_that_filter_returns_expected_table(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        $hashTable['baz'] = 'buz';

        $output = $hashTable->filter(function ($value, $key) {
            return substr($key, 0, 1) === 'b';
        });

        $data = [];

        foreach ($output as $key => $value) {
            $data[$key] = $value;
        }

        self::assertSame(['baz' => 'buz'], $data);
    }

    public function test_that_reject_returns_expected_table(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        $hashTable['baz'] = 'buz';

        $output = $hashTable->reject(function ($value, $key) {
            return substr($key, 0, 1) === 'b';
        });

        $data = [];

        foreach ($output as $key => $value) {
            $data[$key] = $value;
        }

        self::assertSame(['foo' => 'bar'], $data);
    }

    public function test_that_partition_returns_expected_tables(): void
    {
        $hashTable = HashTable::of('string', 'string');
        $hashTable['foo'] = 'bar';
        $hashTable['baz'] = 'buz';

        $parts = $hashTable->partition(function ($value, $key) {
            return substr($key, 0, 1) === 'b';
        });

        $data1 = [];

        foreach ($parts[0] as $key => $value) {
            $data1[$key] = $value;
        }

        $data2 = [];

        foreach ($parts[1] as $key => $value) {
            $data2[$key] = $value;
        }

        self::assertTrue($data1 === ['baz' => 'buz'] && $data2 === ['foo' => 'bar']);
    }

    public function test_that_set_triggers_assert_error_for_invalid_key_type(): void
    {
        self::expectException(AssertionError::class);

        HashTable::of('string', 'string')->set(10, 'foo');
    }

    public function test_that_set_triggers_assert_error_for_invalid_value_type(): void
    {
        self::expectException(AssertionError::class);

        HashTable::of('string', 'string')->set('foo', 10);
    }

    public function test_that_get_throws_exception_for_key_not_found(): void
    {
        self::expectException(SystemException::class);

        HashTable::of('string', 'string')->get('foo');
    }
}
