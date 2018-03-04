<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use Runalyze\Service\ElevationCorrection\Exception\InvalidResponseException;
use Runalyze\Service\ElevationCorrection\Exception\NoResponseException;
use Runalyze\Service\ElevationCorrection\Exception\StrategyException;

abstract class AbstractStrategyFromExternalAPI extends AbstractStrategy implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var int */
    protected $PointsPerCall = 20;

    /** @var Client */
    protected $HttpClient;

    public function __construct(Client $client, LoggerInterface $logger = null)
    {
        $this->HttpClient = $client;
        $this->logger = $logger ?: new NullLogger();
    }

    public function loadAltitudeData(array $latitudes, array $longitudes)
    {
        try {
            $altitudes = $this->collectGroupWiseAltitudes($latitudes, $longitudes);
        } catch (StrategyException $e) {
            return null;
        }

        return $altitudes;
    }

    /**
     * @param float[] $latitudes
     * @param float[] $longitudes
     *
     * @return int[] [m]
     */
    protected function collectGroupWiseAltitudes(array $latitudes, array $longitudes)
    {
        $numberOfPoints = count($latitudes);
        $pointsToGroup  = $this->PointsToGroup * ceil($numberOfPoints / 1000);
        $latitudesForRequest = [];
        $longitudesForRequest = [];
        $altitudes = [];

        for ($i = 0; $i < $numberOfPoints; $i++) {
            if ($i % $pointsToGroup == 0) {
                $latitudesForRequest[] = $latitudes[$i];
                $longitudesForRequest[] = $longitudes[$i];
            }

            if (($i + 1) % ($this->PointsPerCall * $pointsToGroup) == 0 || $i == $numberOfPoints - 1) {
                $result = $this->fetchElevationFor($latitudesForRequest, $longitudesForRequest);
                $points = count($result);

                for ($d = 0; $d < $points; $d++) {
                    for ($j = 0; $j < $pointsToGroup; $j++) {
                        $altitudes[] = $result[$d];
                    }
                }

                $latitudesForRequest = [];
                $longitudesForRequest = [];
            }
        }

        if (count($altitudes) > $numberOfPoints) {
            $altitudes = array_slice($altitudes, 0, $numberOfPoints);
        }

        return $altitudes;
    }

    protected function tryToLoadJsonFromUrl($url)
    {
        try {
            $response = $this->HttpClient->get($url);
            $result = json_decode($response->getBody()->getContents(), true);

            if (is_array($result) && $this->checkApiResult($result)) {
                return $result;
            }
        } catch (RequestException $e) {
            $this->logger->warning('Elevation request failed.', ['exception' => $e]);

            throw new NoResponseException();
        }

        throw new InvalidResponseException();
    }

    /**
     * @param float[] $latitudes
     * @param float[] $longitudes
     *
     * @return int[] [m]
     */
    abstract protected function fetchElevationFor(array $latitudes, array $longitudes);

    /**
     * @param array $json
     *
     * @return bool
     */
    abstract protected function checkApiResult(array $json);
}
