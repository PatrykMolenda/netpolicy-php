<?php

namespace PatrykMolenda\NetPolicy\Render;

final class RenderContext
{
    // todo
    public function __construct(
        protected string $vendor,
        protected string $role,
        protected string $addressFamily
    )
    {
    }
}