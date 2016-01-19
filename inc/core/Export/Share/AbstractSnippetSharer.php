<?php
/**
 * This file contains class::AbstractSnippetSharer
 * @package Runalyze\Export\Share
 */

namespace Runalyze\Export\Share;

/**
 * Exporter for: code snippets
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Share
 */
abstract class AbstractSnippetSharer extends AbstractSharer
{
    /** @var string */
    const URL = 'call/call.Exporter.export.php';

    /**
     * @return string
     */
    public function iconClass()
    {
        return 'fa-code';
    }

    /**
     * @return bool
     */
    public function isExternalLink()
    {
        return false;
    }

    /**
     * @return string
     */
    public function url()
    {
        return self::URL.'?social=true&id='.$this->Context->activity()->id().'&typeid='.$this->enum();
    }

    /**
     * @return int
     */
    abstract public function enum();

    /**
     * Display
     */
    abstract public function display();

    /**
     * @return string
     */
    abstract protected function codeSnippet();
}
