<?php

namespace PatrykMolenda\NetPolicy\Domain;

final class AttributeBag
{
    /**
     * @param array $attributes
     */
    public function __construct(protected array $attributes = []) {}

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->attributes;
    }
}