<?php

namespace Isometriks\JsonLdDumper;

class Dumper implements DumperInterface
{
    private $parser;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function dump($data)
    {
        $parsed = $this->parser->parse($data);

        return $this->dumpJson($parsed);
    }

    private function dumpJson($data)
    {
        $string = '<script type="application/ld+json">' . "\n";
        $string .= json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES) . "\n";
        $string .= '</script>';

        return $string;
    }
}
