<?php

namespace Isometriks\JsonLdDumper\Parser;

use Isometriks\JsonLdDumper\MappingConfiguration;
use Isometriks\JsonLdDumper\Parser\ParserInterface;

class Parser
{
    private $mappingConfiguration;
    private $parsers;

    public function __construct(MappingConfiguration $mappingConfiguration, array $parsers = array())
    {
        $this->mappingConfiguration = $mappingConfiguration;

        foreach ($parsers as $parser) {
            $this->addParser($parser);
        }
    }

    public function addParser(ParserInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    public function parse($mapping, $context = null)
    {
        $parsedValues = array();

        foreach ($mapping as $key => $value) {
            $parsedValues[$key] = $this->doParse($value, $context);
        }

        return $parsedValues;
    }

    private function doParse($value, $context = null)
    {
        $result = $this->subParse($value, $context);

        // Parse any safe arrays
        if (is_array($result)) {
            return $this->parse($result, $context);
        }

        // Parse objects
        if ($this->isMappable($result)) {
            $result = $this->parse(
                $this->mappingConfiguration->getEntityMapping($result),
                $result
            );
        }

        return $result;
    }

    /**
     * @return ReturnValue
     */
    private function subParse($value, $context)
    {
        foreach ($this->parsers as $parser) {
            if (!$parser->canParse($value)) {
                continue;
            }

            $returnValue = $parser->parseValue($value, $context);

            // Make sure it's a ReturnValue
            if (!$returnValue instanceof ReturnValue) {
                throw new \InvalidArgumentException('You must return a "ReturnValue" from a parser');
            }

            if ($returnValue->isSafe()) {
                return $this->subParse($returnValue->getValue(), $context);
            }

            // Stop evaluating, no longer safe.
            return $returnValue->getValue();
        }

        return $value;
    }

    private function isMappable($value)
    {
        return is_object($value) && $this->mappingConfiguration->hasEntityMapping($value);
    }
}
