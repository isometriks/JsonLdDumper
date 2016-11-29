<?php

namespace Isometriks\JsonLdDumper\Parser;

class DateParser implements ParserInterface
{
    public function canParse($value, $context = null)
    {
        return $value instanceof \DateTime;
    }

    public function parseValue($value, $context = null)
    {
        return new ReturnValue($value->format('c'), false);
    }
}
