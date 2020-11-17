# Immutable, type-aware PHP Collection

Collection pattern implementation with map, flatMap, reduce, sort, unique etc. methods. Serializable to JSON.

## Requirements

PHP >= 7.1

## Installation
This package is available on composer via packagist.

`composer require jarjak/collection`

## Basic usage

### Initialization
```php
use JarJak\Collection\Collection;

$dateArray = [
    new DateTime(),
    new DateTimeImmutable(),
];

$dateCollection = new Collection(...$dateArray);
# or
$dateCollection = Collection::from($dateArray);

# then do what you know from other collection implementations
$plusYears = $dateCollection
    ->map(fn($date) => $date->modify('+1 year'))
    ->toArray();
```

### Adding type check feature
```php
use JarJak\Collection\Collection;

class DateCollection extends Collection
{
    // accept only date objects
    public function __construct(DateTimeInterface ...$items)
    {
        parent::__construct(...$items);
    }
    
    // you can personalize it by adding custom methods too!
    public function getMaxYear(): int
    {
        return $this->reduce(
            0, 
            function (int $current, DateTimeInterface $date): int {
                $year = (int) $date->format('Y');
                return $current > $year ? $current : $year; 
            }
        );
    }
}

# this is ok
$dateCollection = new DateCollection(
    new DateTime(),
    new DateTimeImmutable()
);
# this is not
$dateCollection = new DateCollection(
    new DateTime(),
    new DateTimeImmutable(),
    date('now')
);

# then this will work
$plusYears = $dateCollection->map(fn($date) => $date->modify('+1 year'));
# but this won't
$dateStrings = $dateCollection->map(fn($date) => $date->format('Y-m-d'));
# unless you explicitly disable type check (which effectively sends you back to the base Collection class)
$dateStrings = $dateCollection
    ->disableTypeCheck()
    ->map(fn($date) => $date->format('Y-m-d'));
```

## Inspired by

https://github.com/jkoudys/immutable.php
