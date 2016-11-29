<?php

namespace Isometriks\JsonLdDumper\Parser;

final class ReturnValue
{
    private $value;
    private $isSafe;

    public function __construct($value, $isSafe = false)
    {
        $this->value = $value;
        $this->isSafe = $isSafe;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isSafe()
    {
        return $this->isSafe;
    }
}
