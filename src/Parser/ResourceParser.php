<?php

namespace Isometriks\JsonLdDumper\Parser;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ResourceParser implements ParserInterface
{
    private $accessor;

    public function canParse($value, $context = null)
    {
        if (!is_string($value)) {
            return false;
        }

        return substr($value, 0, 10) === '$resource.';
    }

    public function parseValue($value, $context = null)
    {
        $propertyPath = substr($value, 10);
        $accessor = $this->getAccessor();
        $accessedValue = $accessor->getValue($context, $propertyPath);

        // Only allow objects to be further processed, and no __toString
        $isSafe = !is_string($accessedValue) && is_object($accessedValue);

        return new ReturnValue($accessedValue, $isSafe);
    }

    /**
     * @return PropertyAccessor
     */
    private function getAccessor()
    {
        if ($this->accessor === null) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->accessor;
    }
}
