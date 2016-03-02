<?php
/**
 * This file contains class::GooglePlus
 * @package Runalyze\Export\Share
 */

namespace Runalyze\Export\Share;

use System;

/**
 * Exporter for: Google+
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Export\Share
 */
class GooglePlus extends AbstractSocialSharer
{
    /**
     * @return string
     */
    public function iconClass()
    {
        return 'fa-google-plus color-google-plus';
    }

    /**
     * @return bool
     */
    public function isPossible()
    {
        return ($this->Context->activity()->isPublic() && !System::isAtLocalhost());
    }

    /**
     * @return string
     */
    public function url()
    {
        return 'https://plus.google.com/share?url='.urlencode($this->publicURL().'&utm_medium=referral&utm_source=googleplus');
    }

    /**
     * @return string
     */
	public function name()
    {
	    return __('Google+');
	}
}
