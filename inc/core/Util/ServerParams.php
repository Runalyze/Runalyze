<?php

namespace Runalyze\Util;

class ServerParams
{
    /**
     * @return int|null [bytes]
     */
    public function getPostMaxSizeInBytes()
    {
        return $this->transformStringToBytes($this->getNormalizedIniPostMaxSize());
    }

    /**
     * @return int|null [bytes]
     */
    public function getUploadMaxFilesize()
    {
        return $this->transformStringToBytes($this->getNormalizedIniUploadMaxFilesize());
    }

    /**
     * @return string
     */
    public function getNormalizedIniPostMaxSize()
    {
        return strtoupper(trim(ini_get('post_max_size')));
    }

    /**
     * @return string
     */
    public function getNormalizedIniUploadMaxFilesize()
    {
        return strtoupper(trim(ini_get('upload_max_filesize')));
    }

    /**
     * @param string $iniSetting
     * @return null|int [bytes]
     */
    public function transformStringToBytes($iniSetting)
    {
        $iniMax = strtolower($iniSetting);

        if ('' === $iniMax) {
            return null;
        }

        if ('-1' === $iniMax) {
            return PHP_INT_MAX;
        }

        $max = ltrim($iniMax, '+');

        if (0 === strpos($max, '0x')) {
            $max = intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = (int) $max;
        }

        switch (substr($iniMax, -1)) {
            case 't': $max *= 1024;
            case 'g': $max *= 1024;
            case 'm': $max *= 1024;
            case 'k': $max *= 1024;
        }

        return $max;
    }
}
