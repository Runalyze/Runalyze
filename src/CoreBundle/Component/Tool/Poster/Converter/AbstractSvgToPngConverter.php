<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter;

abstract class AbstractSvgToPngConverter
{
    /** @var array */
    protected $Parameter = [];

    /** @var string */
    protected $Command;

    /**
     * @param int|string $height [px]
     */
    abstract public function setHeight($height);

    /**
     * @param int|string $width [px]
     */
    abstract public function setWidth($width);

    /**
     * @param string $source absolute path to source file
     * @param string $target absolute path to target file
     * @return bool true on success
     */
    abstract public function callConverter($source, $target);
}
