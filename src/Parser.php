<?php

namespace Isometriks\JsonLdDumper;

use Isometriks\JsonLdDumper\MappingConfiguration;
use Isometriks\JsonLdDumper\Replacer\ReplacerInterface;
use Isometriks\JsonLdDumper\Replacer\ReturnValue;

class Parser implements ParserInterface
{
    private $mappingConfiguration;
    private $replacers;

    public function __construct(MappingConfiguration $mappingConfiguration, array $replacers = array())
    {
        $this->mappingConfiguration = $mappingConfiguration;

        foreach ($replacers as $replacer) {
            $this->addReplacer($replacer);
        }
    }

    public function addReplacer(ReplacerInterface $replacer)
    {
        $this->replacers[] = $replacer;
    }

    public function parse($mapping, $context = null)
    {
        if (is_array($mapping)) {
            return array_map(function ($value) use ($context) {
                return $this->doParse($value, $context);
            }, $mapping);
        }

        return $this->doParse($mapping, $context);
    }

    private function doParse($value, $context = null)
    {
        $result = $this->replace($value, $context);

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

    private function replace($value, $context)
    {
        foreach ($this->replacers as $replacer) {
            if (!$replacer->canParse($value)) {
                continue;
            }

            $returnValue = $replacer->replace($value, $context);

            // Make sure it's a ReturnValue
            if (!$returnValue instanceof ReturnValue) {
                throw new \InvalidArgumentException(sprintf('You must return a "%s" from a replacer.', ReturnValue::class));
            }

            if ($returnValue->isSafe()) {
                return $this->replace($returnValue->getValue(), $context);
            }

            // Stop evaluating, no longer safe.
            return $returnValue->getValue();
        }

        return $value;
    }

    /**
     * Check if a value is a mappable entity.
     *
     * @param mixed $value
     * @return boolean
     */
    private function isMappable($value)
    {
        return is_object($value) && $this->mappingConfiguration->hasEntityMapping($value);
    }
}
