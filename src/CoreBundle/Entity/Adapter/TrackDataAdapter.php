<?php

namespace Runalyze\Bundle\CoreBundle\Entity\Adapter;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\GradientCalculator;
use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\PaceCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Trackdata;
use Runalyze\Calculation\Math\MovingAverage\Kernel\Uniform;
use Runalyze\Sports\Running\GradeAdjustedPace\Algorithm\Minetti;

class TrackDataAdapter
{
    /** @var TrackData */
    protected $TrackData;

    public function __construct(Trackdata $trackData)
    {
        $this->TrackData = $trackData;
    }

    public function calculatePace()
    {
        $calculator = new PaceCalculator();
        $calculator->calculateFor($this->TrackData);
    }

    public function calculateGradient()
    {
        if (null !== $this->TrackData->getActivity() && $this->TrackData->getActivity()->hasRoute()) {
            $calculator = new GradientCalculator();
            $calculator->calculateFor(
                $this->TrackData,
                $this->TrackData->getActivity()->getRoute(),
                new Uniform(20)
            );
        } else {
            $this->TrackData->setGradient(null);
        }
    }

    public function calculateGradeAdjustedPace()
    {
        $this->TrackData->setGradeAdjustedPace(null);

        if ($this->TrackData->hasPace()) {
            $pace = $this->TrackData->getPace();
            $gradient = $this->TrackData->getGradient();

            if (null !== $gradient) {
                $algorithm = new Minetti();
                $gradeAdjustedPace = $pace;

                foreach (array_keys($gradeAdjustedPace) as $i) {
                    $gradeAdjustedPace[$i] *= $algorithm->getTimeFactor($gradient[$i] / 100.0);
                }

                $this->TrackData->setGradeAdjustedPace($gradeAdjustedPace);
            }
        }
    }

    public function calculateStrideLength()
    {
        if (!$this->TrackData->hasStrideLength()) {
            $this->TrackData->setStrideLength(null);

            return;
        }

        $pace = $this->TrackData->getPace();
        $cadence = $this->TrackData->getCadence();
        $num = count($pace);
        $strideLength = [];

        for ($i = 0; $i < $num; ++$i) {
            $strideLength[] = ($cadence[$i] > 0 && $pace[$i] > 0)
                ? (int)round( 1000 * 100 / ($cadence[$i] * 2 * $pace[$i] / 60))
                : 0;
        }

        $this->TrackData->setStrideLength($strideLength);
    }

    public function calculateVerticalRatio()
    {
        if (!$this->TrackData->hasVerticalRatio()) {
            $this->TrackData->setVerticalRatio(null);

            return;
        }

        $oscillation = $this->TrackData->getVerticalOscillation();
        $strideLength = $this->TrackData->getStrideLength();
        $num = count($oscillation);
        $verticalRatio = [];

        for ($i = 0; $i < $num; ++$i) {
            $verticalRatio[] = ($strideLength[$i] > 0) ? (int)round(100 * $oscillation[$i] / $strideLength[$i]) : 0;
        }

        $this->TrackData->setVerticalRatio($verticalRatio);
    }

    public function getSmoothPace()
    {
        // TODO
    }

    public function getSmoothGradient()
    {
        // TODO
    }
}
