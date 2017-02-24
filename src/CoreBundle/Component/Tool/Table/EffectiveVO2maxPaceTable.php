<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Table;

use Runalyze\Activity\Duration;
use Runalyze\Calculation\JD\VDOT;

class EffectiveVO2maxPaceTable
{
    /**
     * @param int[] $vo2maxValues
     * @return array ['vo2max' => ['label' => 'vo2max', 'paces' => [..., ...], ...]
     */
    public function getVO2maxPaces(array $vo2maxValues)
    {
        $paceDefinitions = $this->getPaces();
        $vo2maxObject = new VDOT;
        $result = [];

        foreach ($vo2maxValues as $vo2max) {
            $vo2maxObject->setValue($vo2max);

            $result[$vo2max] = [
                'value' => $vo2max,
                'paces' => []
            ];

            foreach ($paceDefinitions as $paceDefinition) {
                $result[$vo2max]['paces'][] = Duration::format($vo2maxObject->paceAt($paceDefinition['percent']/100));
            }
        }

        return $result;
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
