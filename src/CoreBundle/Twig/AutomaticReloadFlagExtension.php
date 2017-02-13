<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class AutomaticReloadFlagExtension extends \Twig_Extension
{
    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'runalyze.automatic_reload_flag_extension';
    }

    /**
     * @return \Twig_SimpleFunction[]
     *
     * @codeCoverageIgnore
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('automaticJavaScriptReload', array($this, 'automaticJavaScriptReload')),
        );
    }

    /**
     * @param FlashBag $flashBag
     * @return string
     */
    public function automaticJavaScriptReload(FlashBag $flashBag)
    {
        $reloadFlag = $flashBag->get(AutomaticReloadFlagSetter::FLASH_BAG_KEY);
        $javaScriptCommands = [
            AutomaticReloadFlagSetter::FLAG_DATA_BROWSER => 'Runalyze.DataBrowser.reload();',
            AutomaticReloadFlagSetter::FLAG_TRAINING => 'Runalyze.Training.reload();',
            AutomaticReloadFlagSetter::FLAG_TRAINING_AND_DATA_BROWSER => 'Runalyze.reloadDataBrowserAndTraining();',
            AutomaticReloadFlagSetter::FLAG_PLUGINS => 'Runalyze.reloadAllPlugins();',
            AutomaticReloadFlagSetter::FLAG_ALL => 'Runalyze.reloadContent();',
            AutomaticReloadFlagSetter::FLAG_PAGE => 'Runalyze.reloadPage();'
        ];

        if (!empty($reloadFlag) && isset($javaScriptCommands[$reloadFlag[0]])) {
            return $javaScriptCommands[$reloadFlag[0]];
        }

        return '';
    }
}
