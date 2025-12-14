<?php

namespace PatrykMolenda\NetPolicy\Util;

use PatrykMolenda\NetPolicy\Exception\ValidationException;

final class ArrayAssert
{
    /**
     * @param array $data
     * @param string $key
     * @return void
     * @throws ValidationException
     */
    public static function key(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationException("Missing required key: $key");
        }
    }

    /**
     * @param array $data
     * @param string $key
     * @return void
     * @throws ValidationException
     */
    public static function string(array $data, string $key): void
    {
        self::key($data, $key);
        if (!is_string($data[$key])) {
            throw new ValidationException("Key $key must be a string");
        }
    }

    /**
     * @param array $data
     * @param string $key
     * @return void
     * @throws ValidationException
     */
    public static function int(array $data, string $key): void
    {
        self::key($data, $key);
        if (!is_int($data[$key])) {
            throw new ValidationException("Key $key must be an integer");
        }
    }
}