<?php

namespace Runalyze\Model\Common;

interface WithNullableArraysInterface
{
    /**
     * Ensure that internal arrays are arrays and not null
     */
    public function ensureArraysToBeNotNull();

    /**
     * Ensure that internal arrays are null if they are empty and are marked as possibly null
     */
    public function ensureArraysToBeNullIfEmpty();
}
