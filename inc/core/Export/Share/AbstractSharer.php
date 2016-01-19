<?php
/**
 * This file contains class::AbstractSharer
 * @package Runalyze\Export\Share
 */

namespace Runalyze\Export\Share;

use HTML;
use Runalyze\Export\AbstractExporter;
use Runalyze\View\Activity\Linker;
use System;

/**
 * Create exporter for given type
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export\Share
 */
abstract class AbstractSharer extends AbstractExporter
{
    /**
     * @return bool
     */
    abstract public function isExternalLink();

    /**
     * @return string
     */
    abstract public function url();

    /**
     * @return string
     */
    abstract public function name();

    /**
     * Get text for sharing
     */
    final protected function text() {
        $Text  = __('I did sport: ');

        if ($this->Context->activity()->distance() > 0) {
            $Text .= $this->Context->dataview()->distance().' ';
            $Text .= $this->Context->dataview()->titleByTypeOrSport().__(' in ');
            $Text .= $this->Context->dataview()->duration()->string().' ';
            $Text .= '('.$this->Context->dataview()->pace()->valueWithAppendix().')';
        } else {
            $Text .= $this->Context->dataview()->duration()->string().' ';
            $Text .= $this->Context->dataview()->titleByTypeOrSport();
        }


        if ($this->Context->activity()->comment() != '') {
            $Text .= ' - '.$this->Context->activity()->comment();
        }

        return strip_tags(str_replace('&nbsp;', '', $Text));
    }

    /**
     * @return string
     */
    final protected function publicURL() {
        $Linker = new Linker($this->Context->activity());

        return $Linker->publicUrl();
    }

    /**
     * @param string $text
     * @return string
     */
    final protected function externalLink($text = '') {
        if (empty($text)) {
            $text = $this->name();
        }

        return '<a href="'.$this->url().'" target="_blank" style="display:block!important"><i class="fa '.$this->iconClass().'"></i> <strong>'.$text.'</strong></a>';
    }

    /**
     * Get error for localhost
     * @return string
     */
    final protected function errorForLocalhost() {
        if (System::isAtLocalhost()) {
            return HTML::error(
                __('Runalyze is running on a local server.').' '.
                __('Linking your activity in a social network does not make sense - nobody will be able to see your activity.')
            );
        }

        return '';
    }
}