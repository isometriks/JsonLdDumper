<?php

namespace Isometriks\JsonLdDumper;

use Isometriks\JsonLdDumper\Replacer\ReplacerInterface;

interface ParserInterface
{
    /**
     * Parse a mapping an an optional context.
     *
     * @return array
     */
    public function parse($value, $context = null);
    
    /**
     * Add a replacer
     * @param ReplacerInterface $replacer
     */
    public function addReplacer(ReplacerInterface $replacer);
}
