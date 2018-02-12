<?php

namespace Runalyze\Parser\Common;

trait FileContentAwareParserTrait
{
    /** @var string */
    protected $FileContent;

    /**
     * @param string $content
     */
    public function setFileContent($content)
    {
        $this->FileContent = $content;
    }
}
