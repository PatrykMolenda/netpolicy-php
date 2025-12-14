<?php

namespace PatrykMolenda\NetPolicy\Domain;

final class Rule
{
    /**
     * @param MatchCondition $match
     * @param Action $action
     */
    public function __construct(
        protected MatchCondition $match,
        protected Action $action
    ) {}

    /**
     * @return MatchCondition
     */
    public function match(): MatchCondition
    {
        return $this->match;
    }

    /**
     * @return Action
     */
    public function action(): Action
    {
        return $this->action;
    }
}