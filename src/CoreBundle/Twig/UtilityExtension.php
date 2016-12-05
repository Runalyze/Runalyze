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
            new \Twig_SimpleFilter('duration', array($this, 'duration')),
            new \Twig_SimpleFilter('filesize', array($this, 'filesizeAsString')),
            new \Twig_SimpleFilter('nbsp', array($this, 'nbsp')),
        );
    }

    /**
     * @param float $seconds
     * @param int $decimals
     * @param string $decimalPoint
     * @return string
     */
    public function duration($seconds, $decimals = 0, $decimalPoint = ',')
    {
        $fraction = (round($seconds) != round($seconds, $decimals) && $decimals > 0) ? $decimalPoint.'u' : '';

        if ($seconds>= 86400) {
            return $this->formatDuration($seconds, 'z\d H:i:s');
        } elseif ($seconds >= 3600) {
            return $this->formatDuration($seconds, 'G:i:s');
        } elseif ($seconds < 60) {
            return '0:'.$this->formatDuration($seconds, 's'.$fraction, $decimals);
        }

        return ltrim($this->formatDuration($seconds, 'i:s'.$fraction, $decimals), '0');
    }

    /**
     * @param float $seconds
     * @param string $format
     * @param int $decimals
     * @return string
     */
    private function formatDuration($seconds, $format, $decimals = 0)
    {
        $time = \DateTime::createFromFormat('!U', (int)round($seconds), new \DateTimeZone('UTC'));

        if (substr($format, -1) == 'u') {
            $fraction = str_pad(round(fmod($seconds, 1) * pow(10, $decimals)), $decimals, '0', STR_PAD_LEFT);
            return $time->format(substr($format, 0, -1)).$fraction;
        }

        return $time->format($format);
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

    /**
     * @param string $stringWithSpaces
     * @return mixed
     */
    public function nbsp($stringWithSpaces)
    {
        return str_replace(' ', '&nbsp;', $stringWithSpaces);
    }
}
