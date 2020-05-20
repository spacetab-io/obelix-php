<?php

declare(strict_types=1);

namespace Spacetab\Obelix;

final class ResultSet
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var array<string, mixed>
     */
    private array $map;

    /**
     * ResultSet constructor.
     *
     * @param mixed $value
     * @param array<string, mixed> $map
     */
    public function __construct($value, array $map = [])
    {
        $this->value = $value;
        $this->map   = $map;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
