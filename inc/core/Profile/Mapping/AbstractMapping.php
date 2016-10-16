<?php

namespace Runalyze\Profile\Mapping;

abstract class AbstractMapping implements ToInternalMappingInterface, ToExternalMappingInterface
{
    /** @var int[]|string[] */
    protected $Mapping = [];

    /** @var int[]|string[] */
    protected $FlippedMapping = [];

    public function __construct()
    {
        $this->Mapping = $this->getMapping();
        $this->FlippedMapping = $this->getFlippedMapping();
    }

    /**
     * @param int|string $value
     * @return int|string
     */
    public function toInternal($value)
    {
        if (isset($this->Mapping[$value])) {
            return $this->Mapping[$value];
        }

        return $this->internalDefault();
    }

    /**
     * @param int|string $value
     * @return int|string
     */
    public function toExternal($value)
    {
        if (isset($this->FlippedMapping[$value])) {
            return $this->FlippedMapping[$value];
        }

        return $this->externalDefault();
    }

    /**
     * The most import mapping of each profile must be backward
     * as far as possible to enable flipping of the mapping.
     * @return array
     */
    abstract protected function getMapping();

    /**
     * @return array
     */
    protected function getFlippedMapping()
    {
        return array_flip($this->Mapping);
    }

    /**
     * @return int|string
     */
    abstract protected function internalDefault();

    /**
     * @return int|string
     */
    abstract protected function externalDefault();
}
