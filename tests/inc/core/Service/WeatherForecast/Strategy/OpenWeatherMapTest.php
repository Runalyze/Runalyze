<?php

namespace Runalyze\Tests\Service\WeatherForecast\Strategy;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Runalyze\Profile\Weather\Source\WeatherSourceProfile;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Runalyze\Service\WeatherForecast\Location;
use Runalyze\Service\WeatherForecast\Strategy\OpenWeatherMap;

class OpenWeatherMapTest extends \PHPUnit_Framework_TestCase
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
        $darkSky = new OpenWeatherMap('', $this->getMockForResponses([
            new Response(200, [], '')
        ]));

        $location = new Location();
        $location->setPosition(49.45, 7.75);
        $location->setDateTime(new \DateTime());

        $result = $darkSky->loadForecast($location);

        $this->assertNull($result);
    }

    public function testSimpleResponse()
    {
        $darkSky = new OpenWeatherMap('', $this->getMockForResponses([
            new Response(200, [], '{
				"coord":{"lon":7.75,"lat":49.45},
				"sys":{"message":0.0221,"country":"DE","sunrise":1401334144,"sunset":1401391431},
				"weather":[{"id":803,"main":"Clouds","description":"broken clouds","icon":"04d"}],
				"base":"cmc stations",
				"main":{"temp":290,"pressure":1013.456,"temp_min":16.11,"temp_max":18.2,"humidity":59.24},
				"wind":{"speed":1.76,"deg":313.5},
				"clouds":{"all":76},
				"dt":1401371226,
				"id":2894003,
				"name":"Kaiserslautern",
				"cod":200
			}')
        ]));

        $location = new Location();
        $location->setPosition(49.45, 7.75);
        $location->setDateTime(new \DateTime());

        $result = $darkSky->loadForecast($location);

        $this->assertNotNull($result);

        $this->assertEquals(WeatherConditionProfile::CLOUDY, $result->InternalConditionId);
        $this->assertEquals(6.34, $result->WindSpeed, '', 0.01);
        $this->assertEquals(314, $result->WindDirection);
        $this->assertEquals(59, $result->Humidity);
        $this->assertEquals(1013, $result->AirPressure);
        $this->assertEquals(17.0, $result->Temperature, '', 0.1);
        $this->assertEquals(WeatherSourceProfile::OPEN_WEATHER_MAP, $result->Source);
    }
}
