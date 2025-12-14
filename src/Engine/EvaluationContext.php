<?php

namespace PatrykMolenda\NetPolicy\Engine;

use PatrykMolenda\NetPolicy\Network\AsNumber;
use PatrykMolenda\NetPolicy\Network\Prefix;
use PatrykMolenda\NetPolicy\Network\Protocol;

final class EvaluationContext
{
    /**
     * @param Prefix $prefix
     * @param Protocol $protocol
     * @param AsNumber|null $asn
     * @param string $direction
     */
    public function __construct(
        protected Prefix $prefix,
        protected Protocol $protocol,
        protected ?AsNumber $asn,
        protected string $direction
    ) {}

    /**
     * @return Prefix
     */
    public function ipAddress(): Prefix
    {
        return $this->prefix;
    }

    /**
     * @return Protocol
     */
    public function protocol(): Protocol
    {
        return $this->protocol;
    }

    /**
     * @return AsNumber|null
     */
    public function asNumber(): ?AsNumber
    {
        return $this->asn;
    }

    /**
     * @return string
     */
    public function direction(): string
    {
        return $this->direction;
    }
}