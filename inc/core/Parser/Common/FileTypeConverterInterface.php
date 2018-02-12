<?php

namespace Runalyze\Parser\Common;

interface FileTypeConverterInterface
{
    /**
     * @return string|string[]
     */
    public function getConvertibleFileExtension();

    /**
     * @param string $inputFile
     *
     * @return string|string[]
     */
    public function getConvertedFileName($inputFile);

    /**
     * @param string $inputFile
     *
     * @return string|string[] name of output file(s)
     */
    public function convertFile($inputFile);
}
