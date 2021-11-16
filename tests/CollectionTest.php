<?php

declare(strict_types=1);

namespace Tests;

use JarJak\Collection\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @dataProvider uniqueDataProvider
     */
    public function testUnique($input, $expected)
    {
        $service = new Collection(...$input);
        $this->assertSame($expected, $service->unique()->toArray());
    }

    public function uniqueDataProvider(): iterable
    {
        $testClass = new \stdClass();
        $testClass2 = new \stdClass();

        yield [
            [
                1,
                ['foo' => 'bar'],
                [],
                'a',
                'b',
                $testClass,
                'a',
                [],
                $testClass,
                ['foo' => 'bar'],
                $testClass2,
                'c',
            ],
            [
                1,
                ['foo' => 'bar'],
                [],
                'a',
                'b',
                $testClass,
                $testClass2,
                'c',
            ],
        ];
    }

    public function testSortFlatMap()
    {
        $dateCollection = new class(
            new \DateTimeImmutable('now'),
            new \DateTimeImmutable('-5 years'),
            new \DateTimeImmutable('+5 years')
        ) extends Collection {
            // accept only date objects
            public function __construct(\DateTimeInterface ...$items)
            {
                parent::__construct(...$items);
            }
        };

        $currentYear = (int) date('Y');

        $expected = [
            $currentYear - 6,
            $currentYear - 5,
            $currentYear - 4,
            $currentYear - 1,
            $currentYear,
            $currentYear + 1,
            $currentYear + 4,
            $currentYear + 5,
            $currentYear + 6,
        ];

        $result = $dateCollection
            ->flatMap(function (\DateTimeImmutable $item): array {
                return [
                    $item->modify('-1 year'),
                    $item,
                    $item->modify('+1 year'),
                ];
            })
            ->sort()
            ->disableTypeCheck()
            ->map(fn (\DateTimeImmutable $item): int => (int) $item->format('Y'));

        $this->assertSame($expected, $result->toArray());
    }

    public function testFromConstructor()
    {
        $dates = [
            new \DateTimeImmutable('now'),
            new \DateTimeImmutable('-5 years'),
            new \DateTimeImmutable('+5 years'),
        ];

        $dateCollectionClass = new class() extends Collection {
            // accept only date objects
            public function __construct(\DateTimeImmutable ...$items)
            {
                parent::__construct(...$items);
            }
        };

        $resultFromArray = $dateCollectionClass::from($dates);
        $this->assertSame($dates, $resultFromArray->toArray());

        $resultFromIterable = $dateCollectionClass::from((fn (): iterable => yield from $dates)());
        $this->assertSame($dates, $resultFromIterable->toArray());
    }

    public function testFlatMapOnDeepAssociative()
    {
        $input = [
            ['foo' => 'bar'],
            ['foo' => 'baz'],
        ];
        $expected = ['BAR', 'BAZ'];

        $result = Collection::from($input)
            ->flatMap(function (array $values): array {
                return array_map('strtoupper', $values);
            });

        $this->assertSame($expected, $result->toArray());
    }

    public function testFlatten()
    {
        $input = [
            ['foo' => 'bar'],
            ['foo' => 'baz'],
        ];
        $expected = ['bar', 'baz'];

        $collection = Collection::from($input);
        $result = $collection->flatten();

        $this->assertSame($expected, $result->toArray());
    }

    public function testFirst()
    {
        $collection = Collection::from(range(1, 10));
        $sum = 0;
        foreach ($collection as $item) {
            // just iterate over it
            $sum += $item;
        }

        $this->assertSame($collection->first(), 1);
        $this->assertSame(55, $sum);

        $emptyCollection = new Collection();
        $this->assertSame($emptyCollection->first(), null);
    }

    public function testAdd()
    {
        $collection = Collection::from(range(1, 3));
        $collection = $collection->add(5);
        $collection = $collection->add(5);
        $collection = $collection->add(3);

        $this->assertSame($collection->count(), 6);
        $this->assertSame($collection->toArray(), [1, 2, 3, 5, 5, 3]);
    }

    public function testSlice()
    {
        $emptyCollection = new Collection();
        $collection = new Collection(...['a', 'b', 'c', 'd', 'e']);

        $this->assertSame([], $emptyCollection->slice(null)->toArray());
        $this->assertSame(['a', 'b', 'c', 'd', 'e'], $collection->slice(null)->toArray());

        $this->assertSame([], $emptyCollection->slice(3)->toArray());
        $this->assertSame(['a', 'b', 'c'], $collection->slice(3)->toArray());

        $this->assertSame([], $emptyCollection->slice(null)->toArray());
        $this->assertSame(['c', 'd', 'e'], $collection->slice(null, 2)->toArray());

        $this->assertSame([], $emptyCollection->slice(2, 1)->toArray());
        $this->assertSame(['b', 'c'], $collection->slice(2, 1)->toArray());
    }

    public function testFromAssociativeArray()
    {
        $input = ['foo' => 'bar', 'bar' => 'baz'];

        $collection1 = Collection::from($input);
        $this->assertSame(array_values($input), $collection1->toArray());
        $collection2 = new Collection(...$input);
        $this->assertSame(array_values($input), $collection2->toArray());
    }

    public function testFromAssociativeIterable()
    {
        $inputIterable = function (): iterable {
            yield 'foo' => 'bar';
            yield 'bar' => 'baz';
        };

        $collection1 = Collection::from($inputIterable());
        $this->assertSame(array_values(iterator_to_array($inputIterable())), $collection1->toArray());
        $collection2 = new Collection(...$inputIterable());
        $this->assertSame(array_values(iterator_to_array($inputIterable())), $collection2->toArray());
    }
}
