<?php

namespace Runalyze\View\Activity;

use Runalyze\Activity\PerformanceCondition;
use Runalyze\Activity\Temperature;
use Runalyze\Activity\TrainingEffect;
use Runalyze\Configuration;
use Runalyze\Data\Cadence;
use Runalyze\Data\Weather\WindChillFactor;
use Runalyze\Data\Weather\HeatIndex;
use Runalyze\Model\Activity;
use Runalyze\Model\Factory;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Elevation;
use Runalyze\Activity\Energy;
use Runalyze\Activity\GroundcontactBalance;
use Runalyze\Activity\HeartRate;
use Runalyze\Activity\Pace;
use Runalyze\Activity\StrideLength;
use Runalyze\Activity\VerticalRatio;
use Runalyze\Calculation\JD\LegacyEffectiveVO2max;
use Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector;
use Runalyze\View\Icon\EffectiveVO2maxIcon;
use Runalyze\Context as GeneralContext;
use Runalyze\Util\Time;
use Runalyze\Util\LocalTime;
use Runalyze\View\RpeColor;
use Runalyze\View\Stresscolor;

use SessionAccountHandler;
use SportFactory;
use SearchLink;
use Ajax;
use HTML;

class Dataview
{
    /** @var null|\Runalyze\Model\Activity\Entity */
    protected $Activity;

    /** @var null|\Runalyze\Activity\Duration */
    protected $Duration = null;

    /** @var null|\Runalyze\Activity\Energy */
    protected $Energy = null;

    /** @var null|\Runalyze\Activity\HeartRate */
    protected $HRmax = null;

    /** @var null|\Runalyze\Activity\HeartRate */
    protected $HRavg = null;

    /** @var null|\Runalyze\Activity\Pace */
    protected $Pace = null;

    /** @var null|\Runalyze\Calculation\JD\LegacyEffectiveVO2max */
    protected $VO2max = null;

    /** @var null|\Runalyze\Data\Cadence\AbstractCadence */
    protected $Cadence = null;

    /** @var null|\Runalyze\Activity\StrideLength */
    protected $StrideLength = null;

    /** @var null|\Runalyze\Activity\Elevation */
    protected $Elevation = null;

    /** @var null|\Runalyze\Data\Weather\WindChillFactor */
    protected $WindChillFactor = null;

    /** @var null|HeatIndex */
    protected $HeatIndex = null;

    /**
     * @param \Runalyze\Model\Activity\Entity $activity
     */
    public function __construct(Activity\Entity $activity)
    {
        $this->Activity = $activity;
    }

    /**
     * @param mixed $value
     * @param \Closure $constructor
     * @return mixed
     */
    protected function object(&$value, \Closure $constructor)
    {
        if (null === $value) {
            $value = $constructor($this->Activity);
        }

        return $value;
    }

    /**
     * @return string
     */
    public function titleByTypeOrSport()
    {
        $Factory = new Factory(SessionAccountHandler::getId());

        if ($this->Activity->typeid() != 0) {
            return $Factory->type($this->Activity->typeid())->name();
        }

        return $Factory->sport($this->Activity->sportid())->name();
    }

    /**
     * @return string
     */
    public function titleWithComment()
    {
        if ($this->Activity->title() != '') {
            return $this->titleByTypeOrSport().': '.$this->Activity->title();
        }

        return $this->titleByTypeOrSport();
    }

    /**
     * @param string $format [optional]
     * @return string
     */
    public function date($format = 'd.m.Y')
    {
        if (!is_numeric($this->Activity->timestamp())) {
            return '';
        }

        return (new LocalTime($this->Activity->timestamp()))->format($format);
    }

    /**
     * @return string
     */
    public function daytime()
    {
        if (is_numeric($this->Activity->timestamp())) {
            $time = (new LocalTime($this->Activity->timestamp()))->format('H:i');

            if ($time != '00:00') {
                return $time;
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function dateAndDaytime()
    {
        return $this->date().' '.$this->daytime();
    }

    /**
     * @return string
     */
    public function weekday()
    {
        if (!is_numeric($this->Activity->timestamp())) {
            return '';
        }

        return Time::weekday((new LocalTime($this->Activity->timestamp()))->format('w'));
    }

    /**
     * @return \Runalyze\Activity\Duration
     */
    public function duration()
    {
        return $this->object($this->Duration, function (Activity\Entity $Activity) {
            return new Duration($Activity->duration());
        });
    }

    /**
     * @return string
     */
    public function elapsedTime()
    {
        if ($this->Activity->elapsedTime() < $this->Activity->duration())
            return '-:--:--';

        return Duration::format($this->Activity->elapsedTime());
    }

    /**
     * @param int|null $decimals
     * @return string
     */
    public function distance($decimals = null)
    {
        if (is_null($decimals)) {
            $decimals = Configuration::ActivityView()->decimals();
        }

        if ($this->Activity->distance() > 0) {
            if ($this->Activity->isTrack()) {
                return (new Distance($this->Activity->distance()))->stringMeter();
            }

            return Distance::format($this->Activity->distance(), true, $decimals);
        }

        return '';
    }

    /**
     * @return string
     */
    public function distanceWithoutEmptyDecimals()
    {
        $distance = $this->Activity->distance();
        $decimals = ($distance == floor($distance)) ? 0 : null;

        return $this->distance($decimals);
    }

    /**
     * @return string
     */
    public function distanceOrDuration()
    {
        if ($this->Activity->distance() > 0) {
            return $this->distance();
        }

        return $this->duration()->string();
    }

    /**
     * Get a string for the speed depending on sportid
     *
     * @return \Runalyze\Activity\Pace
     */
    public function pace()
    {
        return $this->object($this->Pace, function (Activity\Entity $Activity) {
            return new Pace($Activity->duration(), $Activity->distance(), SportFactory::getSpeedUnitFor($Activity->sportid()));
        });
    }

    /**
     * @return \Runalyze\Data\Cadence\AbstractCadence
     */
    public function cadence()
    {
        return $this->object($this->Cadence, function (Activity\Entity $Activity) {
            if ($Activity->sportid() == Configuration::General()->runningSport()) {
                return new Cadence\Running($Activity->cadence());
            }

            return new Cadence\General($Activity->cadence());
        });
    }

    /**
     * @return \Runalyze\Activity\StrideLength
     */
    public function strideLength()
    {
        return $this->object($this->StrideLength, function (Activity\Entity $Activity) {
            return new StrideLength($Activity->strideLength());
        });
    }

    /**
     * @return string
     */
    public function trimp()
    {
        $Stress = new Stresscolor($this->Activity->trimp());

        return $Stress->string();
    }

    /**
     * @param bool $valueOnly
     * @return int|null|string
     *
     */
    public function rpe($valueOnly = false)
    {
        if ($valueOnly) {
            return $this->Activity->rpe();
        }

        return (new RpeColor($this->Activity->rpe()))->string();
    }

    /**
     * @return string
     */
    public function fitVO2maxEstimate()
    {
        if ($this->Activity->fitVO2maxEstimate() > 0) {
            return number_format($this->Activity->fitVO2maxEstimate(), 2);
        }

        return '';
    }

    /**
     * @return string
     */
    public function fitRecoveryTime()
    {
        if ($this->Activity->fitRecoveryTime() > 0) {
            $hours = $this->Activity->fitRecoveryTime() / 60;

            if ($hours > 72) {
                return round($hours / 24).'d';
            }

            return round($hours).'h';
        }

        return '';
    }

    /**
     * @return string
     */
    public function fitHRVscore()
    {
        if ($this->Activity->fitHRVscore() > 0) {
            $hue = 128 - 64 * ($this->Activity->fitHRVscore() / 1000);
            $tooltip = Ajax::tooltip('', __('HRV score').': '.round($this->Activity->fitHRVscore()), false, true);

            return '<i class="fa fa-fw fa-dot-circle-o" style="color:hsl('.min(128, max(0, round($hue))).',74%,44%);" '.$tooltip.'></i>';
        }

        return '';
    }

    /**
     * @return string
     */
    public function fitTrainingEffect()
    {
        return TrainingEffect::format($this->Activity->fitTrainingEffect());
    }

    /**
     * @return string
     */
    public function fitPerformanceCondition()
    {
        $start = null !== $this->Activity->fitPerformanceCondition() ? $this->fitPerformanceConditionStart() : '-';
        $end = null !== $this->Activity->fitPerformanceConditionEnd() ? $this->fitPerformanceConditionEnd() : '-';

        return $this->formatTwoPartValue($start, $end);
    }

    /**
     * @return string
     */
    public function fitPerformanceConditionStart()
    {
        return PerformanceCondition::format($this->Activity->fitPerformanceCondition());
    }

    /**
     * @return string
     */
    public function fitPerformanceConditionEnd()
    {
        return PerformanceCondition::format($this->Activity->fitPerformanceConditionEnd());
    }

    /**
     * @return string power with unit
     */
    public function power()
    {
        if ($this->Activity->power() > 0)
            return round($this->Activity->power()).'&nbsp;W';

        return '';
    }

    /**
     * @return string ground contact time with unit
     */
    public function groundcontact()
    {
        if ($this->Activity->groundcontact() > 0)
            return round($this->Activity->groundcontact()).'&nbsp;ms';

        return '';
    }

    /**
     * @return string ground contact balance with unit
     */
    public function groundcontactBalance()
    {
        if ($this->Activity->groundContactBalance() > 0) {
            return GroundcontactBalance::format($this->Activity->groundContactBalance());
        }

        return '';
    }

    /**
     * @return string vertical oscillation with unit
     */
    public function verticalOscillation()
    {
        if ($this->Activity->verticalOscillation() > 0)
            return number_format($this->Activity->verticalOscillation() / 10, 1).'&nbsp;cm';

        return '';
    }

    /**
     * @return string
     */
    public function verticalRatio()
    {
        if ($this->Activity->verticalRatio() > 0) {
            return VerticalRatio::format($this->Activity->verticalRatio());
        }

        return '';
    }

    /**
     * @return string
     */
    public function flightTime()
    {
        $flightTime = $this->Activity->flightTime();

        if (null !== $flightTime) {
            return round($flightTime).'&nbsp;ms';
        }

        return '';
    }

    /**
     * @return string
     */
    public function flightRatio()
    {
        $flightRatio = $this->Activity->flightRatio();

        if (null !== $flightRatio) {
            return number_format(100 * $flightRatio, 1, '.', '').'&nbsp;%';
        }

        return '';
    }

    /**
     * @return string
     */
    public function impactGs()
    {
        return $this->formatRunScribeValues($this->Activity->impactGsLeft(), $this->Activity->impactGsRight());
    }

    /**
     * @return string
     */
    public function brakingGs()
    {
        return $this->formatRunScribeValues($this->Activity->brakingGsLeft(), $this->Activity->brakingGsRight());
    }

    /**
     * @return string
     */
    public function footstrikeType()
    {
        return $this->formatRunScribeValues($this->Activity->footstrikeTypeLeft(), $this->Activity->footstrikeTypeRight(), 0);
    }

    /**
     * @return string
     */
    public function pronationExcursion()
    {
        return $this->formatRunScribeValues($this->Activity->pronationExcursionLeft(), $this->Activity->pronationExcursionRight());
    }

    /**
     * @param null|float|int $left
     * @param null|float|int $right
     * @param int $precision
     * @return string
     */
    protected function formatRunScribeValues($left, $right, $precision = 1)
    {
        return $this->formatTwoPartValue(
            null !== $left ? number_format($left, $precision) : '-',
            null !== $right ? number_format($right, $precision) : '-'
        );
    }

    /**
     * @return \Runalyze\Activity\Energy
     */
    public function energy()
    {
        return $this->object($this->Energy, function (Activity\Entity $Activity) {
            return new Energy($Activity->energy());
        });
    }

    /**
     * @return \Runalyze\Activity\HeartRate
     */
    public function hrMax()
    {
        return $this->object($this->HRmax, function (Activity\Entity $Activity) {
            return new HeartRate($Activity->hrMax(), GeneralContext::Athlete());
        });
    }

    /**
     * @return \Runalyze\Activity\HeartRate
     */
    public function hrAvg()
    {
        return $this->object($this->HRavg, function (Activity\Entity $Activity) {
            return new HeartRate($Activity->hrAvg(), GeneralContext::Athlete());
        });
    }

    /**
     * @return \Runalyze\Activity\Elevation
     */
    public function elevation()
    {
        return $this->object($this->Elevation, function (Activity\Entity $Activity) {
            return new Elevation($Activity->elevation());
        });
    }

    /**
     * @return string gradient in percent with percent sign
     */
    public function gradientInPercent()
    {
        if ($this->Activity->distance() == 0)
            return '-';

        return round($this->Activity->elevation() / $this->Activity->distance() / 10, 2).'&nbsp;&#37;';
    }

    /**
     * @return WindChillFactor
     */
    public function windChillFactor()
    {
        return $this->object($this->WindChillFactor, function (Activity\Entity $Activity) {
            return new WindChillFactor(
                $Activity->weather()->windSpeed(),
                new Temperature($Activity->weather()->temperature()->value()),
                new Pace($Activity->duration(), $Activity->distance())
            );
        });
    }

    /**
     * @return HeatIndex
     */
    public function heatIndex()
    {
        return $this->object($this->HeatIndex, function (Activity\Entity $Activity) {
            return new HeatIndex(
                new \Runalyze\Activity\Temperature($Activity->weather()->temperature()->value()),
                $Activity->weather()->humidity()
            );
        });
    }

    /**
     * @return string
     */
    public function partner()
    {
        return HTML::encodeTags($this->Activity->partner()->asString());
    }

    /**
     * @return string
     */
    public function partnerAsLinks()
    {
        if (\Request::isOnSharedPage()) {
            return $this->partner();
        }

        $links = array();

        foreach ($this->Activity->partner()->asArray() as $partner) {
            $links[] = SearchLink::to('partner', $partner, $partner, 'like');
        }

        return implode(', ', $links);
    }

    /**
     * @return string
     */
    public function notes()
    {
        return nl2br(HTML::encodeTags($this->Activity->notes()));
    }

    /**
     * @return \Runalyze\Calculation\JD\LegacyEffectiveVO2max
     */
    public function vo2max()
    {
        $self = $this;

        return $this->object($this->VO2max, function (Activity\Entity $Activity) use ($self) {
            return new LegacyEffectiveVO2max($self->usedVO2maxValue(), new LegacyEffectiveVO2maxCorrector);
        });
    }

    /**
     * Value of used VO2max (uncorrected)
     *
     * @return float
     */
    public function usedVO2maxValue()
    {
        if (Configuration::VO2max()->useElevationCorrection()) {
            if ($this->Activity->vo2maxWithElevation() > 0) {
                return $this->Activity->vo2maxWithElevation();
            }
        }

        return $this->Activity->vo2maxByHeartRate();
    }

    /**
     * @return string
     */
    public function effectiveVO2maxIcon()
    {
        $value = $this->usedVO2maxValue() * Configuration::Data()->vo2maxCorrectionFactor();

        if ($value > 0) {
            $Icon = new EffectiveVO2maxIcon($value);

            if (!$this->Activity->usesVO2max()) {
                $Icon->setTransparent();
            }

            return $Icon->code();
        }

        return '';
    }

    /**
     * @return string
     */
    public function climbScore()
    {
        if (null !== $this->Activity->climbScore()) {
            return number_format($this->Activity->climbScore(), 1);
        }

        return '';
    }

    /**
     * @return string
     */
    public function percentageHilly()
    {
        if (null !== $this->Activity->percentageHilly()) {
            return (100 * $this->Activity->percentageHilly()).'&nbsp;&#37;';
        }

        return '';
    }

    /**
     * @param string $leftString
     * @param string $rightString
     * @param string $empty
     * @return string
     */
    protected function formatTwoPartValue($leftString, $rightString, $empty = '-')
    {
        if ($empty == $leftString && $empty == $rightString) {
            return '';
        }

        return $leftString.'/'.$rightString;
    }
}
