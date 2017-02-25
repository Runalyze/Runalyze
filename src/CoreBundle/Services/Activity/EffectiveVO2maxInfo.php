<?php

namespace Runalyze\Bundle\CoreBundle\Services\Activity;

use Runalyze\Activity\Distance;
use Runalyze\Activity\Elevation;
use Runalyze\Calculation;
use Runalyze\Calculation\JD;
use Runalyze\Configuration\Category\Data;
use Runalyze\Configuration\Category\Vdot;
use Runalyze\View\Activity\Context;

class EffectiveVO2maxInfo
{
    /** @var null|Context */
    protected $Context = null;

    /** @var null|Data */
    protected $DataConfig = null;

    /** @var null|Vdot */
    protected $VO2maxConfig = null;

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->Context = $context;
    }

    /**
     * @param Data $dataConfig
     * @param Vdot $vo2maxConfig
     *
     * @todo Configuration should be passed via automatic DI as soon as we have a configuration service
     */
    public function setConfiguration(Data $dataConfig, Vdot $vo2maxConfig)
    {
        $this->DataConfig = $dataConfig;
        $this->VO2maxConfig = $vo2maxConfig;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->Context->dataview()->titleWithComment();
    }

    /**
     * @return array details for ['distance', 'duration', 'vo2max']
     *
     * @todo replace dataview() with twig extensions and pass only raw values
     */
    public function getRaceCalculationDetails()
    {
        return [
            'distance' => $this->Context->dataview()->distance(),
            'duration' => $this->Context->dataview()->duration()->string(),
            'vo2max' => number_format($this->Context->activity()->vdotByTime(), 2)
        ];
    }

    /**
     * @return array details for ['hr' (in %HRmax), 'vo2max', 'vVO2max' (in %)]
     */
    public function getHeartRateCalculationDetails()
    {
        $VO2max = new JD\LegacyEffectiveVO2max(
            $this->Context->activity()->vdotByHeartRate(),
            new JD\LegacyEffectiveVO2maxCorrector($this->DataConfig->vdotFactor())
        );
        $vVO2maxinPercent = JD\LegacyEffectiveVO2max::percentageAt($this->Context->activity()->hrAvg() / $this->DataConfig->HRmax());

        return [
            'hr' => $this->Context->dataview()->hrAvg()->inHRmax(),
            'vo2max' => $VO2max->uncorrectedValue(),
            'vVO2max' => round(100 * $vVO2maxinPercent)
        ];
    }

    /**
     * @return array details for ['factor', 'vo2max', 'uncorrected']
     */
    public function getCorrectionFactorDetails()
    {
        $VO2max = new JD\LegacyEffectiveVO2max(
            $this->Context->activity()->vdotByHeartRate(),
            new JD\LegacyEffectiveVO2maxCorrector($this->DataConfig->vdotFactor())
        );

        return [
            'factor' => $this->DataConfig->vdotFactor(),
            'vo2max' => $VO2max->value(),
            'uncorrected' => $VO2max->uncorrectedValue()
        ];
    }

    /**
     * @return array details for ['elevation.up', 'elevation.down', 'vo2max', 'distance.additional', 'distance.total']
     */
    public function getElevationDetails()
    {
        list($up, $down) = $this->getElevationUpAndDown();

        $Modifier = new Calculation\Elevation\DistanceModifier($this->Context->activity()->distance(), $up, $down, $this->VO2maxConfig);

        $VO2max = new JD\LegacyEffectiveVO2max(0, new JD\LegacyEffectiveVO2maxCorrector($this->DataConfig->vdotFactor()));
        $VO2max->fromPaceAndHR(
            $Modifier->correctedDistance(),
            $this->Context->activity()->duration(),
            $this->Context->activity()->hrAvg() / $this->DataConfig->HRmax()
        );

        return [
            'up' => Elevation::format($up, false),
            'down' => Elevation::format($down, true),
            'vo2max' => $VO2max->value(),
            'additionalDistance' => Distance::format($Modifier->additionalDistance(), true, 3),
            'totalDistance' => Distance::format($Modifier->correctedDistance(), true, 3)
        ];
    }

    /**
     * @return array ['up', 'down']
     */
    protected function getElevationUpAndDown()
    {
        if ($this->Context->hasRoute() && ($this->Context->route()->elevationUp() > 0 || $this->Context->route()->elevationDown())) {
            $up = $this->Context->route()->elevationUp();
            $down = $this->Context->route()->elevationDown();
        } else {
            $up = $this->Context->activity()->elevation();
            $down = $up;
        }

        return [$up, $down];
    }

    /**
     * @return bool
     */
    public function usesElevationAdjustment()
    {
        return $this->VO2maxConfig->useElevationCorrection();
    }

    /**
     * @return float [ml/kg/min]
     */
    public function getActivityVO2max()
    {
        if ($this->VO2maxConfig->useElevationCorrection()) {
            $vo2max = $this->Context->activity()->vdotWithElevation();
        } else  {
            $vo2max = $this->Context->activity()->vdotByHeartRate();
        }

        return $vo2max * $this->DataConfig->vdotFactor();
    }
}
