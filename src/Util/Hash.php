<?php

namespace PatrykMolenda\NetPolicy\Util;

use PatrykMolenda\NetPolicy\Domain\Rule;

final class Hash
{
    /**
     * Generate hash for a rule
     *
     * @param Rule $rule
     * @return string
     */
    public static function rule(Rule $rule): string
    {
        $match = $rule->match();
        $action = $rule->action();

        $components = [
            'prefix' => $match->prefix() ? $match->prefix()->cidr() : 'any',
            'asn' => $match->asn() ? (string)$match->asn()->value() : 'any',
            'protocol' => $match->protocol()->value,
            'direction' => $match->direction(),
            'action' => $action->type(),
            'attributes' => json_encode($action->attributes()->all())
        ];

        return md5(json_encode($components));
    }

    /**
     * Generate hash for a policy
     *
     * @param \PatrykMolenda\NetPolicy\Domain\Policy $policy
     * @return string
     */
    public static function policy(\PatrykMolenda\NetPolicy\Domain\Policy $policy): string
    {
        $hashes = [];
        foreach ($policy->rules() as $rule) {
            $hashes[] = self::rule($rule);
        }

        return md5($policy->name() . $policy->priority() . implode('', $hashes));
    }

    /**
     * Generate hash for a policy set
     *
     * @param \PatrykMolenda\NetPolicy\Domain\PolicySet $set
     * @return string
     */
    public static function policySet(\PatrykMolenda\NetPolicy\Domain\PolicySet $set): string
    {
        $hashes = [];
        foreach ($set->policies() as $policy) {
            $hashes[] = self::policy($policy);
        }

        return md5(implode('', $hashes));
    }
}