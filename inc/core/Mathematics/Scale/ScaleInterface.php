<?php

namespace Runalyze\Mathematics\Scale;

interface ScaleInterface
{
    /**
     * @param float|int $input
     * @return float|int scale value
     */
    public function transform($input);
}
