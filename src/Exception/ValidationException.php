<?php

namespace PatrykMolenda\NetPolicy\Exception;

final class ValidationException extends NetPolicyException
{
    public function errors(): array
    {
        return $this->getMessage() ? json_decode($this->getMessage(), true) : [];
    }
}