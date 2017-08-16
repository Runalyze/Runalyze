<?php
/**
 * This file contains class::Tools
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Tools
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Dataset\Keys
 */
class Tools extends AbstractKey
{
    /**
     * Enum id
     * @return int
     */
    public function id()
    {
        return \Runalyze\Dataset\Keys::TOOLS;
    }

    /**
     * Database key
     * @return string
     */
    public function column()
    {
        return ['elevation', 'splits'];
    }

    /**
     * @return bool
     */
    public function isInDatabase()
    {
        return true;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function label()
    {
        return __('Tools');
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function shortLabel()
    {
        return '';
    }

    /**
     * Get string to display this dataset value
     * @param \Runalyze\Dataset\Context $context
     * @return string
     */
    public function stringFor(Context $context)
    {
        if (!\Request::isOnSharedPage() && $context->activity()->id() > 0) {
            return $this->getCodeForDropdown($this->inlineDropdownWithRealLinks($context));
        }

        return '';
    }

    public function stringForExample(Context $context)
    {
        return $this->getCodeForDropdown($this->inlineDropdownWithFakeLinks($context));
    }

    /**
     * @param string $menu
     * @return string
     */
    protected function getCodeForDropdown($menu)
    {
        return '<div class="inline-menu"><ul><li class="with-submenu">'.
            '<span class="link"><i class="fa fa-fw fa-magic"></i></span>'.
            '<ul class="submenu">'.$menu.'</ul>'.
            '</li></ul></div>';
    }

    /**
     * @param \Runalyze\Dataset\Context $context
     * @return string
     * @codeCoverageIgnore
     */
    protected function inlineDropdownWithRealLinks(Context $context)
    {
        $id = $context->activity()->id();
        $html = '';

        if (!$context->activity()->splits()->isEmpty()) {
            $html .= '<li><a class="window" data-size="big" href="activity/'.$id.'/splits-info"><i class="fa fa-fw fa-bar-chart"></i> '.__('Analyze splits').'</a></li>';
        }

        if ($context->activity()->elevation() > 0) {
            $html .= '<li><a class="window" href="activity/'.$id.'/elevation-info"><i class="fa fa-fw fa-area-chart"></i> '.__('Elevation info').'</a></li>';
            $html .= '<li><a class="window" href="activity/'.$id.'/climb-score"><i class="fa fa-fw fa-area-chart"></i> '.__('Climb score').'</a></li>';
        }

        $html .= '<li><a class="window" href="activity/'.$id.'/time-series-info"><i class="fa fa-fw fa-line-chart"></i> '.__('Time series').'</a></li>';
        $html .= '<li><a class="window" href="activity/'.$id.'/sub-segments-info"><i class="fa fa-fw fa-bar-chart"></i> '.__('Sub segments').'</a></li>';

        return $html;
    }

    /**
     * @param \Runalyze\Dataset\Context $context
     * @return string
     * @codeCoverageIgnore
     */
    protected function inlineDropdownWithFakeLinks(Context $context)
    {
        $html = '<li><span class="link"><i class="fa fa-fw fa-bar-chart"></i> '.__('Analyze splits').'</span></li>';
        $html .= '<li><span class="link"><i class="fa fa-fw fa-area-chart"></i> '.__('Elevation info').'</span></li>';
        $html .= '<li><span class="link"><i class="fa fa-fw fa-area-chart"></i> '.__('Climb score').'</span></li>';
        $html .= '<li><span class="link"><i class="fa fa-fw fa-line-chart"></i> '.__('Time series').'</span></li>';
        $html .= '<li><span class="link"><i class="fa fa-fw fa-bar-chart"></i> '.__('Sub segments').'</span></li>';

        return $html;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function cssClass()
    {
        return 'small l';
    }
}
