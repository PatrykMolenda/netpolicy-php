<?php

namespace PatrykMolenda\NetPolicy\Validation\Schema;

use PatrykMolenda\NetPolicy\Exception\ValidationException;

interface SchemaInterface
{
    /**
     * Validate data against schema
     *
     * @param array $data
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $data): bool;

    /**
     * Get schema version
     *
     * @return string
     */
    public function version(): string;

    /**
     * Get schema as array (for JSON Schema export)
     *
     * @return array
     */
    public function toArray(): array;
}