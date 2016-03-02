<?php
/**
 * This file contains class::Twitter
 * @package Runalyze\Export\Share
 */

namespace Runalyze\Export\Share;

use System;

/**
 * Exporter for: Twitter
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Export\Share
 */
class Twitter extends AbstractSocialSharer
{
    /**
     * @return string
     */
    public function iconClass()
    {
        return 'fa-twitter color-twitter';
    }

    /**
     * @return bool
     */
    public function isPossible()
    {
        return true;
    }

    /**
     * @return string
     */
    public function url()
    {
        if ($this->Context->activity()->isPublic() && !System::isAtLocalhost()) {
            $urlPart = 'url='.urlencode($this->publicURL().'&utm_medium=referral&utm_source=twitter').'&';
        } else {
            $urlPart = '';
        }

        return 'https://twitter.com/intent/tweet?'.$urlPart.'text='.$this->text().'&via=RunalyzeDE';
    }

    /**
     * @return string
     */
	public function name()
    {
	    return __('Twitter');
	}
}
