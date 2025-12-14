<?php

namespace PatrykMolenda\NetPolicy\Domain;

final class Action
{
    public const ACCEPT = 'accept';
    public const REJECT = 'reject';
    public const MODIFY = 'modify';

    /**
     * @param string $type
     * @param AttributeBag $attributes
     */
    public function __construct(
        protected string $type,
        protected AttributeBag $attributes
    )
    {
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return AttributeBag
     */
    public function attributes(): AttributeBag
    {
        return $this->attributes;
    }
}