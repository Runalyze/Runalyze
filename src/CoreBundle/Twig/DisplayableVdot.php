<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;

class DisplayableVdot extends DisplayableValue
{
    /** @var float */
    protected $UncorrectedValue;

    /** @var string */
    protected $IconClass;

    /** @var array */
    protected $LowerLimitsForIcon = [3.0, 1.0, -1.0, -3.0];

    /**
     * @param float $uncorrectedValue
     * @param RunalyzeConfigurationList $configurationList
     * @param bool $vdotIsUsedForShape
     */
    public function __construct($uncorrectedValue, RunalyzeConfigurationList $configurationList, $vdotIsUsedForShape = true)
    {
        parent::__construct($uncorrectedValue, '', 2);

        $this->correctValue($configurationList);
        $this->setIconClass($configurationList, $vdotIsUsedForShape);
    }

    /**
     * @return float
     */
    public function getUncorrectedValue()
    {
        return $this->UncorrectedValue;
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return $this->IconClass;
    }

    protected function correctValue(RunalyzeConfigurationList $configurationList)
    {
        $this->UncorrectedValue = $this->Value;
        $this->Value *= $configurationList->getVdotFactor();
    }

    /**
     * @param RunalyzeConfigurationList $configurationList
     * @param bool $vdotIsUsedForShape
     */
    protected function setIconClass(RunalyzeConfigurationList $configurationList, $vdotIsUsedForShape)
    {
        $classes = [
            'vdot-icon',
            'small',
            $this->getIconClassFor($this->Value - $configurationList->getCurrentVdot())
        ];

        if (!$vdotIsUsedForShape) {
            $classes[] = 'unimportant';
        }

        $this->IconClass = implode(' ', $classes);
    }

    /**
     * @param float $diffToShape
     * @return string
     */
    protected function getIconClassFor($diffToShape)
    {
        if ($diffToShape > $this->LowerLimitsForIcon[0]) {
            return 'fa-arrow-up';
        } elseif ($diffToShape > $this->LowerLimitsForIcon[1]) {
            return 'fa-arrow-up  fa-rotate-45';
        } elseif ($diffToShape > $this->LowerLimitsForIcon[2]) {
            return 'fa-arrow-right';
        } elseif ($diffToShape > $this->LowerLimitsForIcon[3]) {
            return 'fa-arrow-right  fa-rotate-45';
        } else {
            return 'fa-arrow-down';
        }
    }
}
