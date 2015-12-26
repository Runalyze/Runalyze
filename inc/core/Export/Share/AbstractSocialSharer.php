<?php
/**
 * This file contains class::AbstractSocialSharer
 * @package Runalyze\Export\Share
 */

namespace Runalyze\Export\Share;

/**
 * Exporter for: social sharing
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Share
 */
abstract class AbstractSocialSharer extends AbstractSharer
{
    /**
     * @return bool
     */
    public function isExternalLink()
    {
        return true;
    }
}
