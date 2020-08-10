<?php

declare(strict_types=1);

namespace Spacetab\Obelix;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

final class Dot
{
    private const DELIMITER = '.';
    private const WILDCARD  = '*';

    /**
     * @var array<mixed>
     */
    private array $items;

    /**
     * @var array<string, mixed>
     */
    private static array $cache = [];

    private string $delimiter = self::DELIMITER;
    private string $wildcard  = self::WILDCARD;

    /**
     * Dot constructor.
     *
     * @param array<mixed>|\Spacetab\Obelix\Dot $items
     */
    public function __construct($items = [])
    {
        if ($items instanceof self) {
            $this->items = $items->toArray();
            self::$cache = $items->getCache();
        } else {
            $this->items = (array) $items;
        }
    }

    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function setWildcard(string $wildcard): void
    {
        $this->wildcard = $wildcard;
    }

    /**
     * @param string $path
     * @param mixed|null $default
     * @return \Spacetab\Obelix\ResultSet
     */
    public function get(string $path, $default = null): ResultSet
    {
        if (isset($this->items[$path])) {
            return new ResultSet($this->items[$path], [$path => $this->items[$path]]);
        }

        if (isset(self::$cache[$path])) {
            return new ResultSet(...self::$cache[$path]);
        }

        $pathway   = [];
        $flatArray = null;

        $segments       = (array) explode($this->delimiter, $path);
        $countSegments  = count($segments);

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

        if (is_countable($value) && count($value) === 1) {
            // @phpstan-ignore-next-line
            $value = $value[0];
        }

        self::$cache[$path] = [$value, $flatArray];

        return new ResultSet($value, $flatArray ?? []);
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
        $equals = false;

        foreach ($user as $item) {
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
