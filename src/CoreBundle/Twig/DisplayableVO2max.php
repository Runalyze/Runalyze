<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;

class DisplayableVO2max extends DisplayableValue
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
     * @param bool $valueIsUsedForShape
     */
    public function __construct($uncorrectedValue, RunalyzeConfigurationList $configurationList, $valueIsUsedForShape = true)
    {
        parent::__construct($uncorrectedValue, '', 2);

        $this->correctValue($configurationList);
        $this->setIconClass($configurationList, $valueIsUsedForShape);
    }

    /**
     * @return float [ml/kg/min]
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
        $this->Value *= $configurationList->getVO2maxCorrectionFactor();
    }

    /**
     * @param RunalyzeConfigurationList $configurationList
     * @param bool $valueIsUsedForShape
     */
    protected function setIconClass(RunalyzeConfigurationList $configurationList, $valueIsUsedForShape)
    {
        $classes = [
            'vo2max-icon',
            'small',
            $this->getIconClassFor($this->Value - $configurationList->getCurrentVO2maxShape())
        ];

        if (!$valueIsUsedForShape) {
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
