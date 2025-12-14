<?php

namespace PatrykMolenda\NetPolicy\Domain;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

final class PolicySet implements IteratorAggregate
{
    /**
     * @var array|Policy[] $policies
     */
    protected array $policies = [];

    /**
     * @param Policy ...$policies
     */
    public function __construct(Policy ...$policies)
    {
        $this->policies = $policies;
    }

    /**
     * @return array|Policy[]
     */
    public function policies(): array
    {
        return $this->policies;
    }

    /**
     * @return self
     */
    public function sorted(): self
    {
        $sorted = $this->policies;
        usort($sorted, fn(Policy $a, Policy $b) => $a->priority() <=> $b->priority());
        return new self(...$sorted);
    }

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->policies);
    }
}