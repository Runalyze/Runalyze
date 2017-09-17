<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Internal\Service;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Data\Weather\Location;
use Runalyze\Service\WeatherForecast\Forecast;
use Runalyze\Util\LocalTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/_internal/service/weather")
 */
class WeatherController extends Controller
{
    /**
     * @Route("", name="internal-service-weather")
     * @Security("has_role('ROLE_USER')")
     */
    public function fetchWeatherDataAction(Request $request, Account $account)
    {
        // As long as forecast uses old db connection for weather cache
        $frontend = new \Frontend(true, $this->get('security.token_storage'));
        $location = $this->getLocationForRequest($request, $account);

        $weather = (new Forecast($location))->object();
        $weather->temperature()->toCelsius();

        return new JsonResponse([
            'empty' => $weather->isEmpty(),
            'location' => [
                'name' => $location->name(),
                'lat' => $location->hasPosition() ? $location->lat() : '',
                'lng' => $location->hasPosition() ? $location->lon() : '',
                'date' => $location->hasDateTime() ? $location->dateTime()->format('c') : ''
            ],
            'source' => [
                'id' => $weather->source(),
                'name' => $weather->sourceAsString()
            ],
            'weatherid' => $weather->condition()->id(),
            'temperature' => $weather->temperature()->value(),
            'wind_speed' => $weather->windSpeed()->value(),
            'wind_deg' => $weather->windDegree()->value(),
            'humidity' => $weather->humidity()->value(),
            'pressure' => $weather->pressure()->value()
        ]);
    }

    /**
     * @param Request $request
     * @param Account $account
     * @return Location
     */
    private function getLocationForRequest(Request $request, Account $account)
    {
        $location = new Location();

        if ($request->query->has('geohash')) {
            $location->setGeohash($request->query->get('geohash'));
        } elseif ($request->query->has('latlng')) {
            $latlng = explode(',', $request->query->get('latlng'));

            if (2 == count($latlng)) {
                $location->setPosition($latlng[0], $latlng[1]);
            }
        }

        if ($request->query->has('city')) {
            $location->setLocationName($request->query->get('city'));
        } elseif (!$location->hasPosition()) {
            $location->setLocationName($this->get('app.configuration_manager')->getList($account)->get('activity-form.PLZ'));
        }

        if ($request->query->has('time')) {
            $location->setDateTime(new \DateTime('@'.$request->query->get('time')));
        } elseif ($request->query->has('date')) {
            $location->setDateTime(new \DateTime($request->query->get('date')));
        }

        return $location;
    }
}
