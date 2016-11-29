<?php

namespace Isometriks\JsonLdDumper\Parser;

interface ParserInterface
{
    /**
     * @return ReturnValue
     */
    public function parseValue($value, $context = null);
    public function canParse($value, $context = null);
}
