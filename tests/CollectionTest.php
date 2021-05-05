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
    public function testUnique($input, $expected): void
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

    public function testSortFlatMap(): void
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

    public function testFirst(): void
    {
        $collection = Collection::from(range(1, 10));
        $sum = 0;
        foreach ($collection as $item) {
            // just iterate over it
            $sum += $item;
        }

        $this->assertSame($collection->first(), 1);
        $this->assertSame(55, $sum);
    }
}
