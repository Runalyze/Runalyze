<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-04-07 at 17:50:49.
 */
class ParserTCXSingleTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ParserTCXSingle
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
		$XML = simplexml_load_string('<test>abc</test>');
		$Parser = new ParserTCXSingle('', $XML);
		$Parser->parse();

		$this->assertTrue( $Parser->failed() );
	}

	public function testStarttime() {
		$XML = simplexml_load_string('<Activity><Id>2011-07-10T09:47:00Z</Id></Activity>');
		$Parser = new ParserTCXSingle('', $XML);
		$Parser->parse();

		$this->assertTrue( $Parser->failed() );
		$this->assertEquals( $Parser->object()->getTimestamp(), mktime(11, 47, 0, 7, 10, 2011) );
	}

	public function testVerySimple() {
		$XML = simplexml_load_string_utf8('
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
				</Activity>');
		$Parser = new ParserTCXSingle('', $XML);
		$Parser->parse();

		$this->assertTrue( !$Parser->failed() );
		$this->assertEquals( $Parser->object()->getTimestamp(), mktime(11, 47, 0, 7, 10, 2011) );
		$this->assertEquals( $Parser->object()->Sport()->id(), CONF_RUNNINGSPORT );
		$this->assertEquals( $Parser->object()->avgHF(), 145 );
		$this->assertEquals( $Parser->object()->Splits()->asString(), '0.20|1:00' );
		$this->assertEquals( $Parser->object()->getArrayAltitude(), array(200, 200) );
		$this->assertEquals( $Parser->object()->getArrayDistance(), array(0.1, 0.2) );
		$this->assertEquals( $Parser->object()->getArrayHeartrate(), array(140, 150) );
		$this->assertEquals( $Parser->object()->getArrayTime(), array(30, 60) );
		$this->assertEquals( $Parser->object()->getCalories(), 16 );
		$this->assertEquals( $Parser->object()->getDistance(), 0.2 );
		$this->assertEquals( $Parser->object()->getPulseAvg(), 145 );
		$this->assertEquals( $Parser->object()->getPulseMax(), 150 );
		$this->assertEquals( $Parser->object()->getTimeInSeconds(), 60 );
		$this->assertEquals( $Parser->object()->hasArrayLatitude(), false );
		$this->assertEquals( $Parser->object()->hasArrayLongitude(), false );
	}

}
