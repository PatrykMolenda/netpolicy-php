<?php

namespace PatrykMolenda\NetPolicy\Domain;

use PatrykMolenda\NetPolicy\Engine\EvaluationContext;
use PatrykMolenda\NetPolicy\Network\AsNumber;
use PatrykMolenda\NetPolicy\Network\Prefix;
use PatrykMolenda\NetPolicy\Network\Protocol;

final class MatchCondition
{
    /**
     * @param Prefix|null $prefix
     * @param AsNumber|null $asn
     * @param Protocol $protocol
     * @param string $direction
     */
    public function __construct(
        protected ?Prefix $prefix,
        protected ?AsNumber $asn,
        protected Protocol $protocol,
        protected string $direction
    ) {}

    /**
     * @param EvaluationContext $ctx
     * @return bool
     */
    public function matches(EvaluationContext $ctx): bool
    {
        // Prefix matching - rule prefix should contain or overlap with context prefix
        $prefixMatches = $this->prefix === null ||
                        $this->prefix->contains($ctx->ipAddress()) ||
                        $this->prefix->overlaps($ctx->ipAddress());

        // ASN matching - both must be non-null to compare, or rule ASN must be null to match any
        $asnMatches = $this->asn === null || ($ctx->asNumber() !== null && $ctx->asNumber()->equals($this->asn));

        // Protocol matching
        $protocolMatches = $this->protocol->equals($ctx->protocol());

        // Direction matching
        $directionMatches = $this->direction === 'any' || $this->direction === $ctx->direction();

        return $prefixMatches && $asnMatches && $protocolMatches && $directionMatches;
    }

    /**
     * @return Prefix|null
     */
    public function prefix(): ?Prefix
    {
        return $this->prefix;
    }

    /**
     * @return AsNumber|null
     */
    public function asn(): ?AsNumber
    {
        return $this->asn;
    }

    /**
     * @return Protocol
     */
    public function protocol(): Protocol
    {
        return $this->protocol;
    }

    /**
     * @return string
     */
    public function direction(): string
    {
        return $this->direction;
    }
}