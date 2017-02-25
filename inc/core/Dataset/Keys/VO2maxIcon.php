<?php

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

class VO2maxIcon extends AbstractKey
{
    /**
     * @return int
     */
    public function id()
    {
        return \Runalyze\Dataset\Keys::VO2MAX_ICON;
    }

    /**
     * @return string
     */
    public function column()
    {
        if (\Runalyze\Configuration::VO2max()->useElevationCorrection()) {
            return 'vdot_with_elevation';
        }

        return 'vdot';
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function label()
    {
        return __('Effective VO<sub>2</sub>max icon');
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function shortLabel()
    {
        return 'VO<sub>2</sub>max';
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function description()
    {
        $text = __(
                'Each VO<sub>2</sub>max value of an activity is marked with an arrow '.
                'to show if the value is (much) higher than your current shape, '.
                'equal to it or (much) lower:'
            ).' ';

        $Icon = new \Runalyze\View\Icon\EffectiveVO2maxIcon;
        $Icon->setUp();
        $text .= $Icon->code();
        $Icon->setUpHalf();
        $text .= $Icon->code();
        $Icon->setRight();
        $text .= $Icon->code();
        $Icon->setDownHalf();
        $text .= $Icon->code();
        $Icon->setDown();
        $text .= $Icon->code();

        return $text;
    }

    /**
     * Get string to display this dataset value
     *
     * @param \Runalyze\Dataset\Context $context
     * @return string
     */
    public function stringFor(Context $context)
    {
        if ($context->isRunning() && $context->dataview()->usedVO2maxValue() > 0) {
            return $context->dataview()->effectiveVO2maxIcon();
        }

        return '';
    }

    /**
     * @return int see \Runalyze\Dataset\SummaryMode for enum
     */
    public function summaryMode()
    {
        return SummaryMode::VO2MAX;
    }
}
