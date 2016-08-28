<?php

namespace Runalyze\Bundle\CoreBundle\Services\Activity;

use Runalyze\Activity\Distance;
use Runalyze\Activity\Elevation;
use Runalyze\Calculation;
use Runalyze\Calculation\JD;
use Runalyze\Configuration\Category\Data;
use Runalyze\Configuration\Category\Vdot;
use Runalyze\View\Activity\Context;

class VdotInfo
{
    /** @var null|Context */
    protected $Context = null;

    /** @var null|Data */
    protected $DataConfig = null;

    /** @var null|Vdot */
    protected $VdotConfig = null;

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->Context = $context;
    }

    /**
     * @param Data $dataConfig
     * @param Vdot $vdotConfig
     *
     * @todo Configuration should be passed via automatic DI as soon as we have a configuration service
     */
    public function setConfiguration(Data $dataConfig, Vdot $vdotConfig)
    {
        $this->DataConfig = $dataConfig;
        $this->VdotConfig = $vdotConfig;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->Context->dataview()->titleWithComment();
    }

    /**
     * @return array details for ['distance', 'duration', 'vdot']
     *
     * @todo replace dataview() with twig extensions and pass only raw values
     */
    public function getRaceCalculationDetails()
    {
        $VDOT = new JD\VDOT($this->Context->activity()->vdotByTime());

        return [
            'distance' => $this->Context->dataview()->distance(),
            'duration' => $this->Context->dataview()->duration()->string(),
            'vdot' => $VDOT->uncorrectedValue()
        ];
    }

    /**
     * @return array details for ['hr' (in %HRmax), 'vdot', 'vVdot' (in %)]
     */
    public function getHeartRateCalculationDetails()
    {
        $VDOT = new JD\VDOT(
            $this->Context->activity()->vdotByHeartRate(),
            new JD\VDOTCorrector($this->DataConfig->vdotFactor())
        );
        $vVDOTinPercent = JD\VDOT::percentageAt($this->Context->activity()->hrAvg() / $this->DataConfig->HRmax());

        return [
            'hr' => $this->Context->dataview()->hrAvg()->inHRmax(),
            'vdot' => $VDOT->uncorrectedValue(),
            'vVdot' => round(100*$vVDOTinPercent)
        ];
    }

    /**
     * @return array details for ['factor', 'vdot', 'uncorrected']
     */
    public function getCorrectionFactorDetails()
    {
        $VDOT = new JD\VDOT(
            $this->Context->activity()->vdotByHeartRate(),
            new JD\VDOTCorrector($this->DataConfig->vdotFactor())
        );

        return [
            'factor' => $this->DataConfig->vdotFactor(),
            'vdot' => $VDOT->value(),
            'uncorrected' => $VDOT->uncorrectedValue()
        ];
    }

    /**
     * @return array details for ['elevation.up', 'elevation.down', 'vdot', 'distance.additional', 'distance.total']
     */
    public function getElevationDetails()
    {
        list($up, $down) = $this->getElevationUpAndDown();

        $Modifier = new Calculation\Elevation\DistanceModifier($this->Context->activity()->distance(), $up, $down, $this->VdotConfig);

        $VDOT = new JD\VDOT(0, new JD\VDOTCorrector($this->DataConfig->vdotFactor()));
        $VDOT->fromPaceAndHR(
            $Modifier->correctedDistance(),
            $this->Context->activity()->duration(),
            $this->Context->activity()->hrAvg() / $this->DataConfig->HRmax()
        );

        return [
            'up' => Elevation::format($up, false),
            'down' => Elevation::format($down, true),
            'vdot' => $VDOT->value(),
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
        return $this->VdotConfig->useElevationCorrection();
    }
}
