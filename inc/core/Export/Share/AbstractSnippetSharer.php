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
    const URL = 'activity/{id}/export/social/{enum}';

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
        return str_replace(
            ['{id}', '{enum}'],
            [$this->Context->activity()->id(), $this->enum()],
            self::URL
        );
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
