<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Table;

use Runalyze\Activity\Duration;
use Runalyze\Calculation\JD\VDOT;
use Runalyze\Configuration;

class VdotPaceTable
{
    /**
     * @param int[] $vdotValues
     * @return array ['vdot' => ['label' => 'vdot', 'paces' => [..., ...], ...]
     */
    public function getVdotPaces(array $vdotValues)
    {
        $paceDefinitions = $this->getPaces();
        $vdotObject = new VDOT;
        $vdots = [];

        foreach ($vdotValues as $vdot) {
            $vdotObject->setValue($vdot);

            $vdots[$vdot] = [
                'value' => $vdot,
                'paces' => []
            ];

            foreach ($paceDefinitions as $paceDefinition) {
                $vdots[$vdot]['paces'][] = Duration::format($vdotObject->paceAt($paceDefinition['percent']/100));
            }
        }

        return $vdots;
    }

    /**
     * @return array
     */
    protected function getPaces()
    {
        return array(
            __('Easy') => array('from' => 59, 'to' => 74, 'percent' => 72.5),
            __('Marathon') => array('from' => 75, 'to' => 84, 'percent' => 86),
            __('Threshold') => array('from' => 83, 'to' => 88, 'percent' => 90),
            __('Interval') => array('from' => 95, 'to' => 100, 'percent' => 97.5),
            __('Repetition') => array('from' => 105, 'to' => 110, 'percent' => 107)
        );
    }
}
