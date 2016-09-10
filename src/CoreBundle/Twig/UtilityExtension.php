<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

class UtilityExtension extends \Twig_Extension
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'runalyze.utility_extension';
    }

    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('filesize', array($this, 'filesizeAsString'))
        );
    }

    /**
     * @param int $bytes
     * @return string
     */
    public function filesizeAsString($bytes)
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $FS = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        return number_format($bytes / pow(1024, $I = floor(log($bytes, 1024))), ($I >= 1) ? 2 : 0, '.', '').' '.$FS[$I];
    }
}
