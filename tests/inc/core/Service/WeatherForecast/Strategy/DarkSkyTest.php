<?php

namespace Runalyze\Tests\Service\WeatherForecast\Strategy;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Runalyze\Profile\Weather\Source\WeatherSourceProfile;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\Service\WeatherForecast\Location;
use Runalyze\Service\WeatherForecast\Strategy\DarkSky;

class DarkSkyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $responses
     * @return Client
     */
    protected function getMockForResponses(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        return new Client([
            'handler' => $handler
        ]);
    }

    public function testEmptyResponse()
    {
        $location = new Location();
        $location->setPosition(54.347, 10.1125);

        $darkSky = new DarkSky('', $this->getMockForResponses([
            new Response(200, [], '')
        ]));

        $result = $darkSky->loadForecast($location);

        $this->assertNull($result);
    }

    public function testSimpleResponse()
    {
        $darkSky = new DarkSky('', $this->getMockForResponses([
            new Response(200, [], '{
               "latitude":54.3470453,
               "longitude":10.1125363,
               "timezone":"Europe/Berlin",
               "offset":2,
               "currently":{
                    "time":1474027196,
                  "summary":"Partly Cloudy",
                  "icon":"partly-cloudy-day",
                  "precipIntensity":0,
                  "precipProbability":0,
                  "temperature":73.2,
                  "apparentTemperature":73.2,
                  "dewPoint":57.78,
                  "humidity":0.59,
                  "windSpeed":11.2,
                  "windBearing":109,
                  "cloudCover":0.38,
                  "pressure":1012.66,
                  "ozone":264.7
               }
		    }')
        ]));

        $location = new Location();
        $location->setPosition(54.347, 10.1125);

        $result = $darkSky->loadForecast($location);

        $this->assertNotNull($result);

        $this->assertEquals(WeatherConditionProfile::CHANGEABLE, $result->InternalConditionId);
        $this->assertEquals(18.02, $result->WindSpeed, '', 0.01);
        $this->assertEquals(109, $result->WindDirection);
        $this->assertEquals(59, $result->Humidity);
        $this->assertEquals(1013, $result->AirPressure);
        $this->assertEquals(22.8, $result->Temperature, '', 0.1);
        $this->assertEquals(WeatherSourceProfile::DARK_SKY, $result->Source);
    }
}
