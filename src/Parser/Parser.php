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
        $parsedMapping = array();

        foreach ($mapping as $key => $value) {
            $currentContext = $context;
            $result = $this->subParse(new ReturnValue($value, true), $currentContext);
            $parsedValue = $result->getValue();

            // Parse sub-objects
            if (is_object($parsedValue) && $this->mappingConfiguration->hasEntityMapping($parsedValue)) {
                $currentContext = $parsedValue;

                $parsedValue = $this->parse(
                    $this->mappingConfiguration->getEntityMapping($parsedValue),
                    $currentContext
                );

                $result = new ReturnValue($parsedValue, true);
            }

            // Parse any safe arrays
            if ($result->isSafe() && is_array($parsedValue)) {
                $parsedValue = $this->parse($parsedValue, $currentContext);
            }

            $parsedMapping[$key] = $parsedValue;
        }

        return $parsedMapping;
    }

    /**
     * @return ReturnValue
     */
    private function subParse(ReturnValue $value, $context)
    {
        foreach ($this->parsers as $parser) {
            if (!$parser->canParse($value->getValue())) {
                continue;
            }

            $value = $parser->parseValue($value->getValue(), $context);

            // Make sure it's a ReturnValue
            if (!$value instanceof ReturnValue) {
                throw new \InvalidArgumentException('You must return a "ReturnValue" from a parser');
            }

            if ($value->isSafe()) {
                return $this->subParse($value, $context);
            }
        }

        return $value;
    }
}
