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
        // Replace values
        $result = $this->replace(new ReturnValue($mapping, true), $context);
        $resultValue = $result->getValue();

        // Parse any safe arrays (array wasn't extracted from an object)
        if ($result->isSafe() && is_array($resultValue)) {
            return array_map(function ($value) use ($context) {
                return $this->parse($value, $context);
            }, $resultValue);
        }

        // Parse objects
        if ($this->isMappable($resultValue)) {
            return $this->parse(
                $this->mappingConfiguration->getEntityMapping($resultValue),
                $resultValue
            );
        }

        return $resultValue;
    }

    private function replace(ReturnValue $value, $context)
    {
        foreach ($this->replacers as $replacer) {
            if (!$replacer->canParse($value->getValue())) {
                continue;
            }

            $returnValue = $replacer->replace($value->getValue(), $context);

            // Make sure it's a ReturnValue
            if (!$returnValue instanceof ReturnValue) {
                throw new \InvalidArgumentException(sprintf('You must return a "%s" from a replacer.', ReturnValue::class));
            }

            if ($returnValue->isSafe()) {
                return $this->replace($returnValue, $context);
            }

            // Stop evaluating, no longer safe.
            return $returnValue;
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
