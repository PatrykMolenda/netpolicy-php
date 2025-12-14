<?php

namespace PatrykMolenda\NetPolicy\Engine;

use PatrykMolenda\NetPolicy\Domain\AttributeBag;
use PatrykMolenda\NetPolicy\Domain\Rule;

final class Decision
{
    /**
     * @param string $action
     * @param AttributeBag $attributes
     * @param Rule $rule
     */
    public function __construct(
        protected string $action,
        protected AttributeBag $attributes,
        protected Rule $rule
    ) {}

    /**
     * @return string
     */
    public function action(): string
    {
        return $this->action;
    }

    /**
     * @return AttributeBag
     */
    public function attributes(): AttributeBag
    {
        return $this->attributes;
    }

    /**
     * @return Rule
     */
    public function rule(): Rule
    {
        return $this->rule;
    }
}