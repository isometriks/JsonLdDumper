<?php

namespace Isometriks\JsonLdDumper\Parser;

use Isometriks\JsonLdDumper\MappingConfiguration;

class StaticParser implements ParserInterface
{
    private $mappingConfiguration;

    public function __construct(MappingConfiguration $mappingConfiguration)
    {
        $this->mappingConfiguration = $mappingConfiguration;
    }

    public function canParse($value, $context = null)
    {
        if (!is_string($value)) {
            return false;
        }

        if (substr($value, 0, 8) !== '$static.') {
            return false;
        }

        return $this->mappingConfiguration->hasStaticMapping(substr($value, 8));
    }

    public function parseValue($value, $context = null)
    {
        $staticMapping = $this->mappingConfiguration->getStaticMapping(substr($value, 8));

        return new ReturnValue($staticMapping, true);
    }
}
