<?php

namespace Isometriks\JsonLdDumper;

class Dumper
{
    private $parser;
    private $mappingConfiguration;

    public function __construct(MappingConfiguration $mappingConfiguration, ParserInterface $parser)
    {
        $this->mappingConfiguration = $mappingConfiguration;
        $this->parser = $parser;
    }

    public function dumpEntity($entity)
    {
        return $this->dumpJson($this->getEntity($entity));
    }

    private function getEntity($entity)
    {
        $mapping = $this->mappingConfiguration->getEntityMapping($entity);
        $parsed = $this->parser->parse($mapping, $entity);

        return $parsed;
    }

    public function dumpStatic($name)
    {
        return $this->dumpJson($this->getStatic($name));
    }

    private function getStatic($name)
    {
        $mapping = $this->mappingConfiguration->getStaticMapping($name);
        $parsed = $this->parser->parse($mapping);

        return $parsed;
    }

    public function dumpArray(array $things)
    {
        return $this->dumpJson(array_map(function($thing) {
            return is_object($thing) ? $this->getEntity($thing) : $this->getStatic($thing);
        }, $things));
    }

    private function dumpJson($data)
    {
        $string = '<script type="application/ld+json">' . "\n";
        $string .= json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES) . "\n";
        $string .= '</script>';

        return $string;
    }
}
