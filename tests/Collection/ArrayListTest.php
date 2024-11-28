<?php

declare(strict_types=1);

namespace Liberty\System\Test\Collection;

use AssertionError;
use Liberty\System\Collection\ArrayList;
use Liberty\System\Exception\SystemException;
use Liberty\System\Exception\UnderflowException;
use Liberty\System\Test\TestCase\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArrayList::class)]
class ArrayListTest extends UnitTestCase
{
    public function test_that_it_is_empty_by_default(): void
    {
        self::assertTrue(ArrayList::of('string')->isEmpty());
    }

    public function test_that_item_type_returns_expected_value(): void
    {
        $itemType = 'string';
        $list = ArrayList::of($itemType);

        self::assertSame($itemType, $list->itemType());
    }

    public function test_that_added_items_affect_count(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('foo');

        self::assertSame(3, count($list));
    }

    public function test_that_added_items_affect_length(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('foo');

        self::assertSame(3, $list->length());
    }

    public function test_that_replace_returns_expected_instance(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');

        self::assertSame(['one', 'two', 'three'], $list->replace(['one', 'two', 'three'])->toArray());
    }

    public function test_that_set_replaces_item_at_an_index(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->set(1, 'baz');

        self::assertSame(2, count($list));
    }

    public function test_that_set_replaces_item_at_a_neg_index(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->set(-1, 'baz');

        self::assertSame('baz', $list->get(1));
    }

    public function test_that_get_returns_item_at_pos_index(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');

        self::assertSame('bar', $list->get(1));
    }

    public function test_that_get_returns_item_at_neg_index(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');

        self::assertSame('bar', $list->get(-2));
    }

    public function test_that_has_returns_true_for_index_in_bounds(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');

        self::assertTrue($list->has(2));
    }

    public function test_that_has_returns_false_for_index_out_of_bounds(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');

        self::assertFalse($list->has(4));
    }

    public function test_that_remove_deletes_item_and_reindexes_list(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');
        $list->remove(1);

        self::assertSame('baz', $list->get(1));
    }

    public function test_that_remove_deletes_correct_item_at_neg_index(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');
        $list->remove(-2);

        self::assertSame('baz', $list->get(1));
    }

    public function test_that_remove_fails_silently_at_out_of_bounds_index(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');
        $list->remove(4);

        self::assertSame(3, count($list));
    }

    public function test_that_contains_returns_true_when_item_is_present_strong_type(): void
    {
        $list = new ArrayList();
        $list->add(1);
        $list->add(2);
        $list->add(3);

        self::assertTrue($list->contains(2));
    }

    public function test_that_contains_returns_false_when_item_is_not_present_strong_type(): void
    {
        $list = new ArrayList();
        $list->add(1);
        $list->add(2);
        $list->add(3);

        self::assertFalse($list->contains(5));
    }

    public function test_that_contains_returns_false_when_item_is_present_different_type(): void
    {
        $list = new ArrayList();
        $list->add(1);
        $list->add(2);
        $list->add(3);

        self::assertFalse($list->contains('2'));
    }

    public function test_that_contains_returns_true_when_item_is_present_weak_type(): void
    {
        $list = new ArrayList();
        $list->add(1);
        $list->add(2);
        $list->add(3);

        self::assertTrue($list->contains('2', $strict = false));
    }

    public function test_that_offset_set_adds_item_when_used_without_index(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';

        self::assertSame('bar', $list[1]);
    }

    public function test_that_offset_set_replaces_item_at_given_index(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[1] = 'baz';

        self::assertSame(2, count($list));
    }

    public function test_that_offset_exists_returns_true_for_index_in_bounds(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';

        self::assertTrue(isset($list[1]));
    }

    public function test_that_offset_exists_returns_false_for_index_out_of_bounds(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';

        self::assertFalse(isset($list[2]));
    }

    public function test_that_offset_unset_deletes_item_and_reindexes_list(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';
        unset($list[1]);

        self::assertSame('baz', $list[1]);
    }

    public function test_that_sort_correctly_sorts_items(): void
    {
        $items = [
            942, 510, 256, 486, 985, 152, 385, 836, 907, 499, 519, 194, 832, 42, 246, 409, 886, 555, 561, 209,
            865, 125, 385, 568, 35, 491, 974, 784, 980, 800, 591, 884, 648, 971, 583, 359, 907, 758, 438, 34,
            398, 855, 364, 236, 817, 548, 518, 369, 817, 887, 559, 941, 653, 421, 19, 71, 608, 316, 151, 296,
            831, 807, 744, 513, 668, 373, 255, 49, 29, 674, 911, 700, 486, 14, 323, 388, 164, 786, 702, 273,
            207, 25, 809, 635, 68, 134, 86, 744, 486, 657, 155, 445, 702, 78, 558, 17, 394, 247, 171, 236
        ];

        $list = ArrayList::of('int');

        foreach ($items as $item) {
            $list->add($item);
        }

        $list = $list->sort(function ($a, $b) {
            if ($a > $b) {
                return -1;
            }
            if ($a < $b) {
                return 1;
            }

            return 0;
        });

        $reverse = $items;
        rsort($reverse);

        self::assertSame($reverse, $list->toArray());
    }

    public function test_that_reverse_returns_expected_instance(): void
    {
        $list = ArrayList::of('string');
        $list->add('foo');
        $list->add('bar');
        $list->add('baz');

        self::assertSame(['baz', 'bar', 'foo'], $list->reverse()->toArray());
    }

    public function test_that_head_returns_expected_item(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame('foo', $list->head());
    }

    public function test_that_tail_returns_expected_list(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame(['bar', 'baz'], $list->tail()->toArray());
    }

    public function test_that_first_returns_default_when_empty_no_predicate(): void
    {
        self::assertSame('default', ArrayList::of('string')->first(null, 'default'));
    }

    public function test_that_first_returns_default_when_empty(): void
    {
        self::assertSame('default', ArrayList::of('string')->first(function (string $item) {
            return str_contains($item, '@');
        }, 'default'));
    }

    public function test_that_first_returns_expected_item(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame('foo', $list->first());
    }

    public function test_that_first_returns_expected_item_with_callback(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame('bar', $list->first(function (string $item) {
            return substr($item, 0, 1) === 'b';
        }));
    }

    public function test_that_last_returns_default_when_empty_no_predicate(): void
    {
        self::assertSame('default', ArrayList::of('string')->last(null, 'default'));
    }

    public function test_that_last_returns_default_when_empty(): void
    {
        self::assertSame('default', ArrayList::of('string')->last(function (string $item) {
            return str_contains($item, '@');
        }, 'default'));
    }

    public function test_that_last_returns_expected_item(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame('baz', $list->last());
    }

    public function test_that_last_returns_expected_item_with_callback(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame('baz', $list->last(function (string $item) {
            return substr($item, 0, 1) === 'b';
        }));
    }

    public function test_that_index_of_returns_expected_index(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame(1, $list->indexOf('bar'));
    }

    public function test_that_index_of_returns_null_when_item_not_found(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertNull($list->indexOf('buz'));
    }

    public function test_that_index_of_returns_expected_index_with_callback(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame(1, $list->indexOf(function (string $item) {
            return $item === 'bar';
        }));
    }

    public function test_that_index_of_returns_null_when_item_not_found_with_callback(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertNull($list->indexOf(function (string $item) {
            return $item === 'buz';
        }));
    }

    public function test_that_last_index_of_returns_expected_index(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';
        $list[] = 'bar';

        self::assertSame(3, $list->lastIndexOf('bar'));
    }

    public function test_that_last_index_of_returns_null_when_item_not_found(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertNull($list->lastIndexOf('buz'));
    }

    public function test_that_last_index_of_returns_expected_index_with_callback(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';
        $list[] = 'bar';

        self::assertSame(3, $list->lastIndexOf(function (string $item) {
            return $item === 'bar';
        }));
    }

    public function test_that_last_index_of_returns_null_when_item_not_found_with_callback(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertNull($list->lastIndexOf(function (string $item) {
            return $item === 'buz';
        }));
    }

    public function test_that_it_is_iterable_forward(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        for ($list->rewind(); $list->valid(); $list->next()) {
            if ($list->key() === 1) {
                self::assertSame('bar', $list->current());
            }
        }
    }

    public function test_that_it_is_iterable_in_reverse(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        for ($list->end(); $list->valid(); $list->prev()) {
            if ($list->key() === 1) {
                self::assertSame('bar', $list->current());
            }
        }
    }

    public function test_that_it_is_directly_traversable(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        foreach ($list as $index => $item) {
            if ($index === 1) {
                self::assertSame('bar', $item);
            }
        }
    }

    public function test_that_unique_returns_expected_list(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'bar';
        $list[] = 'foo';
        $list[] = 'baz';
        $list[] = 'bar';
        $list[] = 'foo';

        self::assertSame(['bar', 'foo', 'baz'], $list->unique()->toArray());
    }

    public function test_that_unique_returns_expected_list_with_callback(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'bar';
        $list[] = 'foo';
        $list[] = 'baz';
        $list[] = 'bar';
        $list[] = 'foo';

        self::assertSame(['bar', 'foo'], $list->unique(function (string $item) {
            return substr($item, 0, 1);
        })->toArray());
    }

    public function test_that_slice_returns_expected_list(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame(['bar', 'baz', 'foo'], $list->slice(1, 3)->toArray());
    }

    public function test_that_page_returns_expected_list(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame(['foo', 'bar', 'baz'], $list->page(2, 3)->toArray());
    }

    public function test_that_max_returns_expected_value(): void
    {
        $list = ArrayList::of('int');
        $list[] = 5356;
        $list[] = 7489;
        $list[] = 8936;
        $list[] = 2345;

        self::assertSame(8936, $list->max());
    }

    public function test_that_max_returns_expected_value_with_callback(): void
    {
        $list = ArrayList::of('array');
        $list[] = ['age' => 19];
        $list[] = ['age' => 32];
        $list[] = ['age' => 26];

        self::assertSame(['age' => 32], $list->max(function (array $data) {
            return $data['age'];
        }));
    }

    public function test_that_min_returns_expected_value(): void
    {
        $list = ArrayList::of('int');
        $list[] = 5356;
        $list[] = 7489;
        $list[] = 8936;
        $list[] = 2345;

        self::assertSame(2345, $list->min());
    }

    public function test_that_min_returns_expected_value_with_callback(): void
    {
        $list = ArrayList::of('array');
        $list[] = ['age' => 19];
        $list[] = ['age' => 32];
        $list[] = ['age' => 26];

        self::assertSame(['age' => 19], $list->min(function (array $data) {
            return $data['age'];
        }));
    }

    public function test_that_sum_returns_expected_value(): void
    {
        $list = ArrayList::of('int');
        $list[] = 1;
        $list[] = 2;
        $list[] = 3;

        self::assertSame(6, $list->sum());
    }

    public function test_that_sum_returns_null_with_empty_list(): void
    {
        self::assertNull(ArrayList::of('int')->sum());
    }

    public function test_that_sum_returns_expected_value_with_callback(): void
    {
        $list = ArrayList::of('array');
        $list[] = ['age' => 19];
        $list[] = ['age' => 32];
        $list[] = ['age' => 26];

        self::assertSame(77, $list->sum(function (array $data) {
            return $data['age'];
        }));
    }

    public function test_that_average_returns_expected_value(): void
    {
        $list = ArrayList::of('int');
        $list[] = 1;
        $list[] = 2;
        $list[] = 3;

        self::assertEquals(2.0, $list->average());
    }

    public function test_that_average_returns_null_with_empty_list(): void
    {
        self::assertNull(ArrayList::of('int')->average());
    }

    public function test_that_average_returns_expected_value_with_callback(): void
    {
        $list = ArrayList::of('array');
        $list[] = ['age' => 18];
        $list[] = ['age' => 31];
        $list[] = ['age' => 26];

        self::assertEquals(25.0, $list->average(function (array $data) {
            return $data['age'];
        }));
    }

    public function test_that_clone_include_nested_collection(): void
    {
        $list = ArrayList::of('int');
        $items = range(0, 9);

        foreach ($items as $num) {
            $list->add($num);
        }

        $copy = clone $list;

        for ($i = 0; $i < 9; $i++) {
            $list->remove($i);
        }

        self::assertSame($items, $copy->toArray());
    }

    public function test_that_calling_key_without_valid_item_returns_null(): void
    {
        $list = ArrayList::of('string');

        self::assertNull($list->key());
    }

    public function test_that_calling_current_without_valid_item_returns_null(): void
    {
        $list = ArrayList::of('string');

        self::assertNull($list->current());
    }

    public function test_that_each_calls_callback_with_each_item(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        $output = ArrayList::of('string');

        $list->each(function ($item) use ($output) {
            $output->add($item);
        });

        self::assertSame(['foo', 'bar', 'baz'], $output->toArray());
    }

    public function test_that_map_returns_expected_list(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        $output = $list->map(function ($item) {
            return strlen($item);
        }, 'int');

        self::assertSame([3, 3, 3], $output->toArray());
    }

    public function test_that_find_returns_expected_item(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        $item = $list->find(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        self::assertSame('bar', $item);
    }

    public function test_that_find_returns_null_when_item_not_found(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        $item = $list->find(function ($item) {
            return substr($item, 0, 1) === 'c';
        });

        self::assertNull($item);
    }

    public function test_that_filter_returns_expected_list(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        $output = $list->filter(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        self::assertSame(['bar', 'baz'], $output->toArray());
    }

    public function test_that_reject_returns_expected_list(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        $output = $list->reject(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        self::assertSame(['foo'], $output->toArray());
    }

    public function test_that_any_returns_true_when_an_item_passes_test(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertTrue($list->any(function ($item) {
            return $item === 'foo';
        }));
    }

    public function test_that_any_returns_false_when_no_item_passes_test(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertFalse($list->any(function ($item) {
            return $item === 'buz';
        }));
    }

    public function test_that_every_returns_true_when_all_items_pass_test(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertTrue($list->every(function ($item) {
            return strlen($item) === 3;
        }));
    }

    public function test_that_every_returns_false_when_an_item_fails_test(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertFalse($list->every(function ($item) {
            return substr($item, 0, 1) === 'b';
        }));
    }

    public function test_that_partition_returns_expected_lists(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        $parts = $list->partition(function ($item) {
            return substr($item, 0, 1) === 'b';
        });

        self::assertTrue($parts[0]->toArray() === ['bar', 'baz'] && $parts[1]->toArray() === ['foo']);
    }

    public function test_that_it_is_json_encodable(): void
    {
        $list = ArrayList::of('string');
        $list[] = 'foo';
        $list[] = 'bar';
        $list[] = 'baz';

        self::assertSame('["foo","bar","baz"]', json_encode($list));
    }

    public function test_that_head_throws_exception_when_empty(): void
    {
        self::expectException(UnderflowException::class);

        ArrayList::of('string')->head();
    }

    public function test_that_tail_throws_exception_when_empty(): void
    {
        self::expectException(UnderflowException::class);

        ArrayList::of('string')->tail();
    }

    public function test_that_add_triggers_assert_error_for_invalid_item_type(): void
    {
        self::expectException(AssertionError::class);

        ArrayList::of('object')->add('string');
    }

    public function test_that_set_triggers_assert_error_for_invalid_item_type(): void
    {
        self::expectException(AssertionError::class);

        $list = ArrayList::of('object');
        $list->add(new \stdClass());
        $list->set(0, 'string');
    }

    public function test_that_offset_set_triggers_assert_error_for_invalid_index_type(): void
    {
        self::expectException(AssertionError::class);

        $list = ArrayList::of('string');
        $list['foo'] = 'bar';
    }

    public function test_that_offset_get_triggers_assert_error_for_invalid_index_type(): void
    {
        self::expectException(AssertionError::class);

        $list = ArrayList::of('string');
        $list->add('foo');
        $list['foo'];
    }

    public function test_that_offset_exists_triggers_assert_error_for_invalid_index_type(): void
    {
        self::expectException(AssertionError::class);

        $list = ArrayList::of('string');
        $list->add('foo');
        isset($list['foo']);
    }

    public function test_that_offset_unset_triggers_assert_error_for_invalid_index_type(): void
    {
        self::expectException(AssertionError::class);

        $list = ArrayList::of('string');
        $list->add('foo');
        unset($list['foo']);
    }

    public function test_that_set_throws_exception_for_invalid_index(): void
    {
        self::expectException(SystemException::class);

        $list = ArrayList::of('object');
        $list->add(new \stdClass());
        $list->set(1, new \stdClass());
    }

    public function test_that_get_throws_exception_for_invalid_index(): void
    {
        self::expectException(SystemException::class);

        $list = ArrayList::of('object');
        $list->add(new \stdClass());
        $list->get(1);
    }
}
