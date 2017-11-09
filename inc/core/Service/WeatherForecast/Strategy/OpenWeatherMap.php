<?php

namespace Runalyze\Service\WeatherForecast\Strategy;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Profile\Weather\Mapping\OpenWeatherMapMapping;
use Runalyze\Profile\Weather\Source\WeatherSourceProfile;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\Service\WeatherForecast\Location;

/**
 * Forecast-strategy for using openweathermap.org
 *
 * This weather forecast strategy uses the api of openweathermap.org
 * To use this api, a location has to be set.
 *
 * @see http://openweathermap.org/weather-conditions
 */
class OpenWeatherMap implements StrategyInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var string */
    const URL = 'http://api.openweathermap.org/data/2.5/weather';

    /** @var string */
    const URL_HISTORICAL = 'http://api.openweathermap.org/data/2.5/history';

    /** @var string */
    protected $ApiKey;

    /** @var Client */
    protected $HttpClient;

    /**
     * @param string $apiKey
     * @param Client $client
     * @param LoggerInterface|null $logger
     */
    public function __construct($apiKey, Client $client, LoggerInterface $logger = null)
    {
        $this->ApiKey = $apiKey;
        $this->HttpClient = $client;
        $this->logger = $logger;
    }

    public function isPossible()
    {
        return strlen($this->ApiKey) > 0;
    }

    public function isCachable()
    {
        return true;
    }

    public function loadForecast(Location $location)
    {
        $result = $this->tryToLoadForecast($location);

        if (!is_array($result) || empty($result)) {
            return null;
        }

        $this->updateLocationFromResult($location, $result);

        return $this->getWeatherDataFromResult($result);
    }

    /**
     * @param array $result
     *
     * @return WeatherData
     */
    protected function getWeatherDataFromResult(array $result)
    {
        $data = new WeatherData();
        $data->Source = WeatherSourceProfile::OPEN_WEATHER_MAP;

        if (isset($result['main'])) {
            $this->setMainConditions($data, $result['main']);
        }

        if (isset($result['wind'])) {
            $this->setWindConditions($data, $result['wind']);
        }

        $data->InternalConditionId = $this->getInternalConditionId($result);

        return $data;
    }

    protected function setMainConditions(WeatherData $data, array $main)
    {
        if (isset($main['temp'])) {
            $data->Temperature = (int)round($main['temp'] - 273.15);
        }

        if (isset($main['pressure'])) {
            $data->AirPressure = (int)round($main['pressure']);
        }

        if (isset($main['humidity'])) {
            $data->Humidity = (int)round($main['humidity']);
        }
    }

    protected function setWindConditions(WeatherData $data, array $wind)
    {
        if (isset($wind['speed'])) {
            $data->WindSpeed = 3.6 * $wind['speed'];
        }

        if (isset($wind['deg'])) {
            $data->WindDirection = (int)round($wind['deg']);
        }
    }

    /**
     * @param array $result
     *
     * @return int
     */
    protected function getInternalConditionId(array $result)
    {
        if (isset($result['weather']) && isset($result['weather'][0]) && isset($result['weather'][0]['id'])) {
            return (new OpenWeatherMapMapping())->toInternal($result['weather'][0]['id']);
        }

        return WeatherConditionProfile::UNKNOWN;
    }

    /**
     * @param Location $location
     *
     * @return array
     */
    protected function tryToLoadForecast(Location $location)
    {
        if (!$location->isOlderThan(7200)) {
            try {
                $response = $this->HttpClient->get($this->getUrlFor($location));
                $result = json_decode($response->getBody()->getContents(), true);

                if (is_array($result)) {
                    if (isset($result['list']) && !empty($result['list'])) {
                        return $result['list'][0];
                    }

                    if (isset($result)) {
                        return $result;
                    }
                }
            } catch (RequestException $e) {
                $this->logger->warning('OpenWeatherMap API request failed.', ['exception' => $e]);
            }
        }

        return [];
    }

    /**
     * @param Location $location
     *
     * @return string
     */
    protected function getUrlFor(Location $location)
    {
        $parameter = [];

        if ($location->hasPosition()) {
            $parameter[] = 'lat='.$location->getLatitude();
            $parameter[] = 'lon='.$location->getLongitude();
        } else {
            $parameter[] = 'q='.$location->getLocationName();
        }

        return sprintf(
            '%s?%s&APPID=%s',
            self::URL,
            implode('&', $parameter),
            $this->ApiKey
        );
    }

    protected function updateLocationFromResult(Location $location, array $result)
    {
        if (isset($result['coord']) && isset($result['coord']['lat']) && isset($result['coord']['lon'])) {
            $location->setPosition($result['coord']['lat'], $result['coord']['lon']);
        }

        if (isset($result['dt']) && is_numeric($result['dt'])) {
            $location->setDateTime((new \DateTime())->setTimestamp($result['dt']));
        }
    }
}
