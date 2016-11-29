<?php

namespace Isometriks\JsonLdDumper;

class MappingConfiguration
{
    private $static;
    private $entities;

    public function __construct(array $static = array(), array $entities = array())
    {
        $this->static = $static;
        $this->entities = $entities;
    }

    public function setEntityMapping($className, $mapping)
    {
        $this->entities[$className] = $mapping;
    }

    public function setStaticMapping($name, $mapping)
    {
        $this->static[$name] = $mapping;
    }

    public function getEntityMappings()
    {
        return $this->entities;
    }

    public function getEntityMapping($entity)
    {
        foreach ($this->entities as $className => $entityMapping) {
            if ($entity instanceof $className) {
                return $entityMapping;
            }
        }

        throw new \InvalidArgumentException(sprintf('Cannot find mapping for entity of type "%s".', get_class($entity)));
    }

    public function hasEntityMapping($entity)
    {
        foreach ($this->entities as $className => $entityMapping) {
            if ($entity instanceof $className) {
                return true;
            }
        }

        return false;
    }

    public function getStaticMappings()
    {
        return $this->static;
    }

    public function hasStaticMapping($name)
    {
        return isset($this->static[$name]);
    }

    public function getStaticMapping($name)
    {
        if (!$this->hasStaticMapping($name)) {
            throw new \InvalidArgumentException(sprintf('Static mapping "%s" does not exist.', $name));
        }

        return $this->static[$name];
    }
}
