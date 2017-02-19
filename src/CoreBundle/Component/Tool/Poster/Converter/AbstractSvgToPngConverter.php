<?php


namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter;


abstract class AbstractSvgToPngConverter
{

    abstract protected function setHeight($height);
    abstract protected function setWidth($width);

}