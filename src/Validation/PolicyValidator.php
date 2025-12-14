<?php

namespace PatrykMolenda\NetPolicy\Validation;

use PatrykMolenda\NetPolicy\Domain\PolicySet;
use PatrykMolenda\NetPolicy\Exception\ValidationException;

final class PolicyValidator
{
    /**
     * @param PolicySet $set
     * @return void
     * @throws ValidationException
     */
    public function validate(PolicySet $set): void
    {
        if (empty($set->policies())) {
            throw new ValidationException('Policy set cannot be empty');
        }

        foreach ($set->policies() as $policy) {
            if (empty($policy->name())) {
                throw new ValidationException('Policy name cannot be empty');
            }

            if (empty($policy->rules())) {
                throw new ValidationException(
                    sprintf('Policy "%s" must have at least one rule', $policy->name())
                );
            }
        }
    }
}