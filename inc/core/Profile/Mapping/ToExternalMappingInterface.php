<?php

namespace Runalyze\Profile\Mapping;

interface ToExternalMappingInterface
{
    /**
     * @param int|string $value
     * @return int|string
     */
    public function toExternal($value);
}
