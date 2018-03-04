<?php

namespace Runalyze\Parser\Common;

use Runalyze\Parser\Activity\Common\ParserInterface;

interface FileNameAwareParserInterface extends ParserInterface
{
    /**
     * @param string $file
     */
    public function setFileName($file);
}
