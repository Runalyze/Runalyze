<?php

namespace Runalyze\Tests\Service\ElevationCorrection\Strategy;

use GuzzleHttp\Psr7\Response;
use Runalyze\Service\ElevationCorrection\Strategy\Geonames;
use Runalyze\Tests\Service\HttpClientAwareTestCaseTrait;

class GeonamesTest extends \PHPUnit_Framework_TestCase
{
    use HttpClientAwareTestCaseTrait;

    public function testEmptyResponse()
    {
        $geonames = new Geonames('foobar', $this->getMockForResponses([
            new Response(200, [], '')
        ]));

        $this->assertNull($geonames->loadAltitudeData([49.444], [7.769]));
    }

    public function testLimitExceededResponse()
    {
        $geonames = new Geonames('foobar', $this->getMockForResponses([
            new Response(200, [], '{"status":{"message":"the daily limit of 30000 credits for demo has been exceeded. Please use an application specific account. Do not use the demo account for your application.","value":18}}')
        ]));

        $this->assertNull($geonames->loadAltitudeData([49.444], [7.769]));
    }

    /**
     * @see http://api.geonames.org/srtm3JSON?lats=50.01,51.01&lngs=10.2,11.2&username=demo
     */
    public function testSimpleResponse()
    {
        $geonames = new Geonames('foobar', $this->getMockForResponses([
            new Response(200, [], '{"geonames":[{"srtm3":206,"lng":"10.2","lat":"50.01"},{"srtm3":239,"lng":"11.2","lat":"51.01"}],"numDistinctTiles":"2","credits":"0.44000000000000006","numDistinctPositions":"2"}')
        ]));
        $geonames->setPointsToGroup(1);

        $this->assertEquals([206, 239], $geonames->loadAltitudeData([50.01, 51.01], [10.2, 11.2]));
    }
}
