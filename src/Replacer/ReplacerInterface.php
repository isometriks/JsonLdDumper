<?php

namespace Isometriks\JsonLdDumper\Replacer;

interface ReplacerInterface
{
    /**
     * @return ReturnValue
     */
    public function replace($value, $context = null);
    public function canParse($value, $context = null);
}
