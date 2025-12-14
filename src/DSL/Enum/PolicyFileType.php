<?php

namespace PatrykMolenda\NetPolicy\DSL\Enum;

enum PolicyFileType: string
{
    const json = 'json';
    const yaml = 'yaml';
    const xml = 'xml';
}
