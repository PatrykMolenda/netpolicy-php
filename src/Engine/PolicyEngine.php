<?php

namespace PatrykMolenda\NetPolicy\Engine;

use PatrykMolenda\NetPolicy\Domain\Action;
use PatrykMolenda\NetPolicy\Domain\AttributeBag;
use PatrykMolenda\NetPolicy\Domain\PolicySet;
use PatrykMolenda\NetPolicy\Domain\Rule;

final class PolicyEngine
{
    /**
     * Evaluate policy set against evaluation context
     * Returns first matching rule decision
     *
     * @param PolicySet $set
     * @param EvaluationContext $context
     * @return Decision
     */
    public function evaluate(
        PolicySet $set,
        EvaluationContext $context
    ): Decision
    {
        // Sort policies by priority (ascending)
        $sorted = $set->sorted();

        // Evaluate each policy in priority order
        foreach ($sorted->policies() as $policy) {
            foreach ($policy->rules() as $rule) {
                if ($rule->match()->matches($context)) {
                    // First match wins
                    return new Decision(
                        $rule->action()->type(),
                        $rule->action()->attributes(),
                        $rule
                    );
                }
            }
        }

        // No match - return default deny
        return $this->defaultDecision();
    }

    /**
     * Default decision when no rules match
     *
     * @return Decision
     */
    protected function defaultDecision(): Decision
    {
        // Create a default reject action
        $defaultAction = new Action(Action::REJECT, new AttributeBag());

        // Create a dummy rule for the default decision
        $defaultMatch = new \PatrykMolenda\NetPolicy\Domain\MatchCondition(
            null,
            null,
            \PatrykMolenda\NetPolicy\Network\Protocol::BGP,
            'any'
        );
        $defaultRule = new Rule($defaultMatch, $defaultAction);

        return new Decision(
            Action::REJECT,
            new AttributeBag(),
            $defaultRule
        );
    }
}