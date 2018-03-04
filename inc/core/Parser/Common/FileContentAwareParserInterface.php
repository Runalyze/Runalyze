<?php

namespace Runalyze\Parser\Common;

use Runalyze\Parser\Activity\Common\ParserInterface;

interface FileContentAwareParserInterface extends ParserInterface
{
    /**
     * @param string $content
     */
    public function setFileContent($content);
}
