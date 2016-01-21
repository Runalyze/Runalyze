<?php
/**
 * This file contains class::Facebook
 * @package Runalyze\Export\Share
 */

namespace Runalyze\Export\Share;

use System;

/**
 * Exporter for: Facebook
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Export\Share
 */
class Facebook extends AbstractSocialSharer
{
    /**
     * APP-ID
     * @var string
     */
    public static $APP_ID = '473795412675725';

    /**
     * @return string
     */
    public function iconClass()
    {
        return 'fa-facebook color-facebook';
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
        // TODO
        // check: on runalyze.com
        // access_token, see http://www.espend.de/artikel/facebook-api-oauth-access-token-generieren.html
        // publish, call https://graph.facebook.com/me/fitness.runs?access_token=XXX&method=POST&course=...
        // for testing, see https://developers.facebook.com/tools/explorer/473795412675725/?path=me%2Ffitness.runs&method=POST
        // @see https://developers.facebook.com/docs/reference/opengraph/action-type/fitness.runs
        // @see https://developers.facebook.com/docs/reference/opengraph/object-type/fitness.course

        $url   = urlencode($this->publicURL());
        $title = urlencode($this->Context->dataview()->titleWithComment());
        $text  = urlencode($this->text());
        $image = System::getFullDomain(true).'web/assets/images/runalyze.png';

        if (System::isAtLocalhost()) {
            $image = 'https://runalyze.com/web/assets/images/runalyze.png';
        }

        return 'https://www.facebook.com/dialog/feed?app_id='.self::$APP_ID.'&link='.$url.'&picture='.$image.'&name='.$title.'&caption='.$url.'&description='.$text.'&redirect_uri=http://www.facebook.com';
        //$FbUrl = 'https://facebook.com/sharer.php?s=100&amp;p[url]='.$url.'&amp;p[title]='.$title.'&amp;p[summary]='.$text.'&amp;p[images][0]='.$image;
    }

    /**
     * @return string
     */
    public function name()
    {
	    return __('Facebook');
    }
        
    /**
     * Get meta title
     * @return string
     */
    public function metaTitle() {
            return $this->text();
    }
}
