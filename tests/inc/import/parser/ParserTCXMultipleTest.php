<?php

use Runalyze\Configuration;
use Runalyze\Util\LocalTime;

/**
 * @group dependsOn
 * @group dependsOnOldFactory
 */
class ParserTCXMultipleTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ParserTCXMultiple
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() { }

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() { }

	public function testEmpty() {
		$Parser = new ParserTCXMultiple('<test>abc</test>');
		$Parser->parse();

		$this->assertTrue( !$Parser->failed() );
		$this->assertEquals( 0, $Parser->numberOfTrainings());
	}

	public function testVerySimpleSingle() {
		$XML = '
			<root>
			<Activities>
			<Activity Sport="Running" xmlns="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd">
				<Id>2011-07-10T09:47:00Z</Id>
					<Lap StartTime="2011-07-10T09:47:00Z">
						<TotalTimeSeconds>60</TotalTimeSeconds>
						<DistanceMeters>200</DistanceMeters>
						<Calories>16</Calories>
						<Intensity>Active</Intensity>
						<Track>
							<Trackpoint>
								<Time>2011-07-10T09:47:00Z</Time>
								<AltitudeMeters>200</AltitudeMeters>
								<DistanceMeters>0</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>0</Value>
								</HeartRateBpm>
							</Trackpoint>
							<Trackpoint>
								<Time>2011-07-10T09:47:30Z</Time>
								<AltitudeMeters>200</AltitudeMeters>
								<DistanceMeters>100</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>140</Value>
								</HeartRateBpm>
							</Trackpoint>
							<Trackpoint>
								<Time>2011-07-10T09:48:00Z</Time>
								<AltitudeMeters>200</AltitudeMeters>
								<DistanceMeters>200</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>150</Value>
								</HeartRateBpm>
							</Trackpoint>
						</Track>
					  </Lap>
				</Activity>
				</Activities>
				</root>';
		$Parser = new ParserTCXMultiple($XML);
		$Parser->parse();

		$Objects = $Parser->objects();
		$Object = $Objects[0];

		$this->assertTrue( !$Parser->failed() );
		$this->assertEquals( 1, $Parser->numberOfTrainings() );
		$this->assertEquals( $Object->getTimestamp(), LocalTime::mktime(11, 47, 0, 7, 10, 2011) );
		$this->assertEquals( $Object->Sport()->id(), Configuration::General()->runningSport() );
		$this->assertEquals( $Object->avgHF(), 145 );
		$this->assertEquals( $Object->Splits()->asString(), '0.200|1:00' );
		$this->assertEquals( $Object->getArrayAltitude(), array(200, 200) );
		$this->assertEquals( $Object->getArrayDistance(), array(0.1, 0.2) );
		$this->assertEquals( $Object->getArrayHeartrate(), array(140, 150) );
		$this->assertEquals( $Object->getArrayTime(), array(30, 60) );
		$this->assertEquals( $Object->getCalories(), 16 );
		$this->assertEquals( $Object->getDistance(), 0.2 );
		$this->assertEquals( $Object->getPulseAvg(), 145 );
		$this->assertEquals( $Object->getPulseMax(), 150 );
		$this->assertEquals( $Object->getTimeInSeconds(), 60 );
		$this->assertEquals( $Object->hasArrayLatitude(), false );
		$this->assertEquals( $Object->hasArrayLongitude(), false );
	}

	public function testVerySimpleDouble() {
		$XML = '
			<root>
			<Activities>
			<Activity Sport="Running" xmlns="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd">
				<Id>2011-07-10T09:47:00Z</Id>
					<Lap StartTime="2011-07-10T09:47:00Z">
						<TotalTimeSeconds>60</TotalTimeSeconds>
						<DistanceMeters>200</DistanceMeters>
						<Calories>16</Calories>
						<Intensity>Active</Intensity>
						<Track>
							<Trackpoint>
								<Time>2011-07-10T09:47:00Z</Time>
								<AltitudeMeters>200</AltitudeMeters>
								<DistanceMeters>0</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>0</Value>
								</HeartRateBpm>
							</Trackpoint>
							<Trackpoint>
								<Time>2011-07-10T09:47:30Z</Time>
								<AltitudeMeters>200</AltitudeMeters>
								<DistanceMeters>100</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>140</Value>
								</HeartRateBpm>
							</Trackpoint>
							<Trackpoint>
								<Time>2011-07-10T09:48:00Z</Time>
								<AltitudeMeters>200</AltitudeMeters>
								<DistanceMeters>200</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>150</Value>
								</HeartRateBpm>
							</Trackpoint>
						</Track>
					  </Lap>
				</Activity>
			<Activity Sport="Running" xmlns="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd">
				<Id>2011-07-10T09:47:00Z</Id>
					<Lap StartTime="2011-07-10T09:47:00Z">
						<TotalTimeSeconds>60</TotalTimeSeconds>
						<DistanceMeters>500</DistanceMeters>
						<Calories>20</Calories>
						<Intensity>Active</Intensity>
						<Track>
							<Trackpoint>
								<Time>2011-07-10T09:47:00Z</Time>
								<AltitudeMeters>100</AltitudeMeters>
								<DistanceMeters>0</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>0</Value>
								</HeartRateBpm>
							</Trackpoint>
							<Trackpoint>
								<Time>2011-07-10T09:47:40Z</Time>
								<AltitudeMeters>100</AltitudeMeters>
								<DistanceMeters>300</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>100</Value>
								</HeartRateBpm>
							</Trackpoint>
							<Trackpoint>
								<Time>2011-07-10T09:48:00Z</Time>
								<AltitudeMeters>100</AltitudeMeters>
								<DistanceMeters>500</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>130</Value>
								</HeartRateBpm>
							</Trackpoint>
						</Track>
					  </Lap>
				</Activity>
				</Activities>
				</root>';
		$Parser = new ParserTCXMultiple($XML);
		$Parser->parse();

		$Objects = $Parser->objects();
		$Object = $Objects[0];
		$Object2 = $Objects[1];

		$this->assertTrue( !$Parser->failed() );
		$this->assertEquals( 2, $Parser->numberOfTrainings() );

		$this->assertEquals( $Object->getTimestamp(), LocalTime::mktime(11, 47, 0, 7, 10, 2011) );
		$this->assertEquals( $Object->Sport()->id(), Configuration::General()->runningSport() );
		$this->assertEquals( $Object->avgHF(), 145 );
		$this->assertEquals( $Object->Splits()->asString(), '0.200|1:00' );
		$this->assertEquals( $Object->getArrayAltitude(), array(200, 200) );
		$this->assertEquals( $Object->getArrayDistance(), array(0.1, 0.2) );
		$this->assertEquals( $Object->getArrayHeartrate(), array(140, 150) );
		$this->assertEquals( $Object->getArrayTime(), array(30, 60) );
		$this->assertEquals( $Object->getCalories(), 16 );
		$this->assertEquals( $Object->getDistance(), 0.2 );
		$this->assertEquals( $Object->getPulseAvg(), 145 );
		$this->assertEquals( $Object->getPulseMax(), 150 );
		$this->assertEquals( $Object->getTimeInSeconds(), 60 );
		$this->assertEquals( $Object->hasArrayLatitude(), false );
		$this->assertEquals( $Object->hasArrayLongitude(), false );

		$this->assertEquals( $Object2->getTimestamp(), LocalTime::mktime(11, 47, 0, 7, 10, 2011) );
		$this->assertEquals( $Object2->Sport()->id(), Configuration::General()->runningSport() );
		$this->assertEquals( $Object2->avgHF(), 110 );
		$this->assertEquals( $Object2->Splits()->asString(), '0.500|1:00' );
		$this->assertEquals( $Object2->getArrayAltitude(), array(100, 100) );
		$this->assertEquals( $Object2->getArrayDistance(), array(0.3, 0.5) );
		$this->assertEquals( $Object2->getArrayHeartrate(), array(100, 130) );
		$this->assertEquals( $Object2->getArrayTime(), array(40, 60) );
		$this->assertEquals( $Object2->getCalories(), 20 );
		$this->assertEquals( $Object2->getDistance(), 0.5 );
		$this->assertEquals( $Object2->getPulseAvg(), 110 );
		$this->assertEquals( $Object2->getPulseMax(), 130 );
		$this->assertEquals( $Object2->getTimeInSeconds(), 60 );
		$this->assertEquals( $Object2->hasArrayLatitude(), false );
		$this->assertEquals( $Object2->hasArrayLongitude(), false );
	}

}
