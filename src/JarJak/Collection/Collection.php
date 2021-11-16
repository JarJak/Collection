<?php

declare(strict_types=1);

namespace JarJak\Collection;

class Collection extends \SplFixedArray implements \JsonSerializable
{
    public function __construct(...$items)
    {
        parent::__construct(\count($items));

        foreach (array_values($items) as $i => $item) {
            $this[$i] = $item;
        }
    }

    public static function from(iterable $array)
    {
        if (\is_array($array) || $array instanceof \ArrayAccess) {
            return new static(...$array);
        }

        return new static(...$array);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * Run before map or flatMap to accept other collection items than parent collection
     */
    public function disableTypeCheck(): self
    {
        return new self(...$this);
    }

    /**
     * Closure result must be of type accepted by this collection, unless disableTypeCheck() has been called before
     */
    public function map(\Closure $callback)
    {
        return static::from(array_map($callback, $this->toArray()));
    }

    /**
     * Closure result must be an iterable of types accepted by this collection, unless disableTypeCheck() has been called before
     */
    public function flatMap(\Closure $callback)
    {
        return static::from(
            array_merge(
                ...array_map(
                    'array_values',
                    array_map(
                        $callback,
                        $this->toArray()
                    )
                )
            )
        );
    }

    public function flatten()
    {
        return static::from(
            array_merge(
                ...array_map(
                    'array_values',
                    $this->toArray()
                )
            )
        );
    }

    public function reduce($initial, \Closure $reductor)
    {
        return array_reduce($this->toArray(), $reductor, $initial);
    }

    public function filter(?\Closure $callback = null)
    {
        if (null === $callback) {
            $callback = fn ($a): bool => null !== $a;
        }

        return static::from(array_filter($this->toArray(), $callback, ARRAY_FILTER_USE_BOTH));
    }

    public function add($item): self
    {
        $newIndex = $this->count();

        return static::from($this->toArray() + [
            $newIndex => $item,
        ]);
    }

    public function remove($element)
    {
        $array = $this->toArray();
        foreach (array_keys($array, $element, true) as $key) {
            unset($array[$key]);
        }

        return static::from($array);
    }

    /**
     * @param callable|null $comparator fn($a,$b) must return true if same elements, false otherwise
     */
    public function unique(?callable $comparator = null)
    {
        if (null === $comparator) {
            $comparator = fn ($a, $b): bool => $a === $b;
        }

        $newCount = 0;
        $unique = new \SplFixedArray($this->count());

        foreach ($this as $el) {
            for ($i = $newCount; $i >= 0; $i--) {
                // going from back to forward to find sequence of duplicates faster
                if ($comparator($unique[$i], $el)) {
                    continue 2;
                }
            }
            $unique[$newCount++] = $el;
        }

        $unique->setSize($newCount);

        return static::from($unique);
    }

    public function sort(?callable $comparator = null): self
    {
        $array = $this->toArray();

        if ($comparator) {
            usort($array, $comparator);
        } else {
            sort($array);
        }

        return static::from($array);
    }

    public function first()
    {
        if ($this->isEmpty()) {
            return null;
        }

        if ($this instanceof \IteratorAggregate) {
            // PHP 8: SplFixedArray no longer implements Iterator, only IteratorAggregate
            $iterator = $this->getIterator();
        } else {
            $iterator = $this;
        }

        $iterator->rewind();

        return $iterator->current();
    }

    public function slice(?int $maxItems, int $itemsToSkipFromBeginning = 0): self
    {
        return static::from(array_slice($this->toArray(), $itemsToSkipFromBeginning, $maxItems));
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
