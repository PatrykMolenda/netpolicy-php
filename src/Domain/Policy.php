<?php

namespace PatrykMolenda\NetPolicy\Domain;

final class Policy
{
    /**
     * @var array|Rule[] $rules
     */
    protected array $rules = [];

    /**
     * @param string $name
     * @param int $priority
     * @param Rule ...$rules
     */
    public function __construct(
        protected string $name,
        protected int $priority,
        Rule ... $rules
    )
    {
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function priority(): int
    {
        return $this->priority;
    }

    /**
     * @return array|Rule[]
     */
    public function rules(): array
    {
        return $this->rules;
    }
}