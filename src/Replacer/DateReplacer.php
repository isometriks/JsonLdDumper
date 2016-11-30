<?php

namespace Isometriks\JsonLdDumper\Replacer;

class DateReplacer implements ReplacerInterface
{
    private $format;

    public function __construct($format = 'c')
    {
        $this->format = $format;
    }

    public function canParse($value, $context = null)
    {
        return $value instanceof \DateTime;
    }

    public function replace($value, $context = null)
    {
        return new ReturnValue($value->format($this->format), false);
    }
}
