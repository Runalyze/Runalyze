<?php

/**
 * @deprecated since v3.1
 */
class FrontendShared extends Frontend
{
    /** @var boolean */
    public static $IS_SHOWN = false;

    public function displayHeader()
    {
        self::$IS_SHOWN  = true;
    }

    public function displayFooter()
    {
    }

    protected function initSessionAccountHandler()
    {
    }
}
