<?php

namespace PatrykMolenda\NetPolicy\DSL;

use PatrykMolenda\NetPolicy\DSL\Enum\PolicyFileType;
use PatrykMolenda\NetPolicy\Exception\ValidationException;

final class PolicyLoader
{
    /**
     * @param string $content
     * @return string<PolicyFileType>
     */
    protected function detectFormat(string $content): string
    {
        if(str_starts_with(trim($content), '{')) {
            return 'json';
        } elseif (str_starts_with(trim($content), '<')) {
            return 'xml';
        } else {
            return 'yaml';
        }
    }

    /**
     * @param string $path
     * @return array
     * @throws ValidationException
     */
    public function loadFile(string $path): array
    {
        $file = @file_get_contents($path);
        if(!$file) {
            throw new ValidationException("Could not read policy file at path: $path");
        }

        return $this->loadString($file);
    }

    /**
     * @param string $input
     * @return array
     * @throws ValidationException
     */
    public function loadString(string $input): array
    {
        $format = $this->detectFormat($input);

        switch ($format) {
            case PolicyFileType::json:
                return json_decode($input, true);
            case PolicyFileType::yaml:
                return yaml_parse($input);
            case PolicyFileType::xml:
                $xml = simplexml_load_string($input);
                return json_decode(json_encode($xml), true);
            default:
                throw new ValidationException("Unsupported policy format: $format");
        }
    }
}