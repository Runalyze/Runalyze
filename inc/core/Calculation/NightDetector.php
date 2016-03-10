<?php
/**
 * This file contains class::NightDetector
 * @package Runalyze\Calculation
 */

namespace Runalyze\Calculation;

use League\Geotools\Coordinate\CoordinateInterface;
use League\Geotools\Geohash\Geohash;
use Runalyze\Model\Activity;
use Runalyze\Model\Route;
use Runalyze\View\Activity\Context;

/**
 * Night detector
 *
 * Detect if an activity was at night (between sunset and sunrise) or not.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation
 */
class NightDetector
{
    /** @var float */
    const ZENITH = 90.833333333333;

    /**
     * @var null|int null: unknown, 0: at sunlight, 1: at night
     */
    protected $Value = null;

    /**
     * NightDetector constructor
     * @param bool|int $timestamp
     * @param \League\Geotools\Coordinate\CoordinateInterface|null $coordinate
     * @param int $offset
     */
    public function __construct($timestamp = false, CoordinateInterface $coordinate = null, $offset = 0)
    {
        if ($timestamp !== false && null !== $coordinate) {
            $this->setFrom($timestamp, $coordinate, $offset);
        }
    }

    /**
     * @param int $timestamp
     * @param \League\Geotools\Coordinate\CoordinateInterface $coordinate
     * @param int $offset
     * @return \Runalyze\Calculation\NightDetector $this-reference
     * @throws \InvalidArgumentException
     */
    public function setFrom($timestamp, CoordinateInterface $coordinate, $offset = 0)
    {
        if (!is_numeric($timestamp)) {
            throw new \InvalidArgumentException('Provided timestamp must be numerical.');
        }

        $isAfterSunset = $timestamp > date_sunset($timestamp, SUNFUNCS_RET_TIMESTAMP, $coordinate->getLatitude(), $coordinate->getLongitude(), self::ZENITH, $offset);
        $isBeforeSunrise = $timestamp < date_sunrise($timestamp, SUNFUNCS_RET_TIMESTAMP, $coordinate->getLatitude(), $coordinate->getLongitude(), self::ZENITH, $offset);

        $this->Value = $isAfterSunset || $isBeforeSunrise;

        return $this;
    }

    /**
     * @param \Runalyze\Model\Activity\Entity $activity
     * @param \Runalyze\Model\Route\Entity $route
     * @param int $offset
     * @return \Runalyze\Calculation\NightDetector $this-reference
     */
    public function setFromEntities(Activity\Entity $activity, Route\Entity $route, $offset = 0)
    {
        if ($route->hasGeohashes() && $route->get(Route\Entity::STARTPOINT) != '') {
            $timestamp = $activity->timestamp() + 0.5 * $activity->duration();
            $coordinate = (new Geohash())->decode($route->get(Route\Entity::STARTPOINT))->getCoordinate();

            $this->setFrom($timestamp, $coordinate, $offset);
        } else {
            $this->Value = null;
        }

        return $this;
    }

    /**
     * @param \Runalyze\View\Activity\Context $context
     * @param int $offset
     * @return \Runalyze\Calculation\NightDetector $this-reference
     */
    public function setFromContext(Context $context, $offset = 0)
    {
        if (!$context->hasRoute()) {
            $this->Value = null;

            return $this;
        }

        return $this->setFromEntities($context->activity(), $context->route(), $offset);
    }

    /**
     * @return bool
     */
    public function isKnown()
    {
        return (null !== $this->Value);
    }

    /**
     * @return bool
     */
    public function isNight()
    {
        return (1 == $this->Value);
    }

    /**
     * @return int|null null: unknown, 0: at sunlight, 1: at night
     */
    public function value()
    {
        return $this->Value;
    }
}