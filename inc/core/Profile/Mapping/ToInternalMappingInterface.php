<?php

namespace Runalyze\Profile\Mapping;

interface ToInternalMappingInterface
{
    /**
     * @param int|string $value
     * @return int|string
     */
    public function toInternal($value);
}
