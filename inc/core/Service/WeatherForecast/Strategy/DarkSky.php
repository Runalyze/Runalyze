<?php

namespace Runalyze\Service\WeatherForecast\Strategy;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Runalyze\Parser\Activity\Common\Data\WeatherData;
use Runalyze\Profile\Weather\Mapping\DarkskyMapping;
use Runalyze\Profile\Weather\Source\WeatherSourceProfile;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\Service\WeatherForecast\Location;

/**
 * Forecast-strategy for using darksky
 *
 * This weather forecast strategy uses the api of forecast.io
 * API documentation: https://darksky.net/dev/
 * To use this api, a location has to be set.
 *
 * @see https://developer.forecast.io/docs/v2
 */
class DarkSky implements StrategyInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var string */
    const URL = 'https://api.darksky.net/forecast/';

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
        $this->logger = $logger ?: new NullLogger();
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

        return $this->getWeatherDataFromResult($result['currently']);
    }

    /**
     * @param array $currently
     *
     * @return WeatherData
     */
    protected function getWeatherDataFromResult(array $currently)
    {
        $data = new WeatherData();
        $data->Source = WeatherSourceProfile::DARK_SKY;

        if (isset($currently['temperature'])) {
            $data->Temperature = ($currently['temperature'] - 32.0) / 1.8;
        }

        if (isset($currently['pressure'])) {
            $data->AirPressure = (int)round($currently['pressure']);
        }

        if (isset($currently['humidity'])) {
            $data->Humidity = (int)round($currently['humidity'] * 100);
        }

        if (isset($currently['windSpeed'])) {
            $data->WindSpeed = $currently['windSpeed'] / 0.621371192;
        }

        if (isset($currently['windBearing'])) {
            $data->WindDirection = (int)round($currently['windBearing']);
        }

        $data->InternalConditionId = $this->getInternalConditionId($currently);

        return $data;
    }

    /**
     * @param array $currently
     *
     * @return int
     */
    protected function getInternalConditionId(array $currently)
    {
        if (isset($currently['icon'])) {
            return (new DarkskyMapping())->toInternal($currently['icon']);
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
        if ($location->hasPosition()) {
            $url = sprintf(
                '%s/%s,%s,%s',
                self::URL.$this->ApiKey,
                (string)$location->getLatitude(),
                (string)$location->getLongitude(),
                $location->hasDateTime() ? (string)$location->getTimestamp() : time()
            );

            try {
                $response = $this->HttpClient->get($url);
                $result = json_decode($response->getBody()->getContents(), true);

                if (is_array($result) && isset($result['currently'])) {
                    return $result;
                }
            } catch (RequestException $e) {
                $this->logger->warning('DarkSky API request failed.', ['exception' => $e]);
            }
        }

        return [];
    }

    protected function updateLocationFromResult(Location $location, array $result)
    {
        if (isset($result['latitude']) && isset($result['longitude'])) {
            $location->setPosition($result['latitude'], $result['longitude']);
        }

        if (isset($result['dt']) && is_numeric($result['dt'])) {
            $location->setDateTime((new \DateTime())->setTimestamp($result['dt']));
        }
    }
}
