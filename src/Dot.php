<?php

declare(strict_types=1);

namespace Spacetab\Obelix;

use InvalidArgumentException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

final class Dot
{
    private const DELIMITER = '.';
    private const WILDCARD  = '*';

    /** @var array<mixed> */
    private array $items;

    /** @var array<string, mixed> */
    private static array $cache = [];

    /** @var non-empty-string */
    private string $delimiter = self::DELIMITER;

    /** @var non-empty-string */
    private string $wildcard  = self::WILDCARD;

    /**
     * Dot constructor.
     *
     * @param array<mixed>|Dot $items
     */
    public function __construct($items = [])
    {
        if (is_array($items)) {
            $this->items = $items;
        } elseif (/** @infection-ignore-all */ $items instanceof self) {
            $this->items = $items->toArray();
            self::$cache = $items->getCache();
        } else {
            throw new InvalidArgumentException('The items argument must be an array or Dot instance.');
        }
    }

    public function setDelimiter(string $delimiter): void
    {
        if ($delimiter === '') {
            throw new InvalidArgumentException('The delimiter must not be an empty string.');
        }

        $this->delimiter = $delimiter;
    }

    public function setWildcard(string $wildcard): void
    {
        if ($wildcard === '') {
            throw new InvalidArgumentException('The wildcard must not be an empty string.');
        }

        $this->wildcard = $wildcard;
    }

    /**
     * @param string $path
     * @param mixed|null $default
     *
     * @return ResultSet
     */
    public function get(string $path, $default = null): ResultSet
    {
        if (isset($this->items[$path])) {
            return new ResultSet($this->items[$path], [$path => $this->items[$path]]);
        }

        if ($path === $this->wildcard) {
            return new ResultSet($this->items, [$path => $this->items]);
        }

        if (isset(self::$cache[$path])) {
            return new ResultSet(...self::$cache[$path]);
        }

        $pathway   = [];
        $flatArray = null;

        $segments      = explode($this->delimiter, $path);
        $countSegments = count($segments);

        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->items), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($it as $key => $value) {
            $pathway[$it->getDepth()] = $key;

            if ($it->getDepth() + 1 !== $countSegments) {
                continue;
            }

            if ($this->isUserPathEqualsRealPath($segments, $pathway)) {
                $flatArray[
                    implode($this->delimiter, array_slice($pathway, 0, $it->getDepth() + 1))
                ] = $value;
            }
        }

        $value = $flatArray === null ? $default : array_values($flatArray);
        $flatArray = $flatArray ?? [$path => $default];

        if (is_countable($value) && count($value) === 1) {
            // @phpstan-ignore-next-line
            $value = $value[0];
        }

        self::$cache[$path] = [$value, $flatArray];

        return new ResultSet($value, $flatArray);
    }

    /**
     * @param array<mixed> $user
     * @param array<mixed> $real
     * @return bool
     */
    private function isUserPathEqualsRealPath(array $user, array $real): bool
    {
        if ($user === $real) {
            return true;
        }

        $i = 0;
        // @infection-ignore-all
        $equals = false;

        foreach ($user as $item) {
            // @infection-ignore-all
            $val = $real[$i] ?? false;

            // to work with integer indexes in string path (for cases like "foo.0")
            if (ctype_digit($item)) {
                $item = (int) $item;
            }

            if ($val === $item) {
                $equals = true;
            } elseif ($item === $this->wildcard) {
                $equals = true;
            } else {
                return false;
            }

            $i++;
        }

        return $equals;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCache(): array
    {
        return self::$cache;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function __destruct()
    {
        self::$cache = [];
        $this->items = [];
    }
}
