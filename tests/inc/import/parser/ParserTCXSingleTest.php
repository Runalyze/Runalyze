<?php

use Runalyze\Configuration;
use Runalyze\Util\LocalTime;

/**
 * @group dependsOn
 * @group dependsOnOldFactory
 */
class ParserTCXSingleTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var ParserTCXSingle
	 */
	protected $object;

	/**
	 * @expectedException \Runalyze\Import\Exception\ParserException
	 */
	public function testEmpty() {
		$XML = simplexml_load_string('<test>abc</test>');
		$Parser = new ParserTCXSingle('', $XML);
		$Parser->parse();
	}

	public function testVerySimple() {
		$XML = simplexml_load_string_utf8('
			<Activity Sport="Running" xmlns="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd" xmlns:ns3="http://www.garmin.com/xmlschemas/ActivityExtension/v2">
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
								<Cadence>0</Cadence>
								<Extensions>
									<ns3:TPX>
										<ns3:Watts>0</ns3:Watts>
									</ns3:TPX>
								</Extensions>
							</Trackpoint>
							<Trackpoint>
								<Time>2011-07-10T09:47:30Z</Time>
								<AltitudeMeters>200</AltitudeMeters>
								<DistanceMeters>100</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>140</Value>
								</HeartRateBpm>
								<Cadence>80</Cadence>
								<Extensions>
									<ns3:TPX>
										<ns3:Watts>200</ns3:Watts>
									</ns3:TPX>
								</Extensions>
							</Trackpoint>
							<Trackpoint>
								<Time>2011-07-10T09:48:00Z</Time>
								<AltitudeMeters>200</AltitudeMeters>
								<DistanceMeters>200</DistanceMeters>
								<HeartRateBpm xsi:type="HeartRateInBeatsPerMinute_t">
									<Value>150</Value>
								</HeartRateBpm>
								<Extensions>
									<ns3:TPX>
										<ns3:Watts>240</ns3:Watts>
									</ns3:TPX>
									<TPX xmlns="http://www.garmin.com/xmlschemas/ActivityExtension/v2" CadenceSensor="Footpod">
										<RunCadence>100</RunCadence>
									</TPX>
								</Extensions>
							</Trackpoint>
						</Track>
					  </Lap>
				</Activity>');
		$Parser = new ParserTCXSingle('', $XML);
		$Parser->parse();

		$this->assertTrue( !$Parser->failed() );
		$this->assertEquals( $Parser->object()->getTimestamp(), LocalTime::mktime(11, 47, 0, 7, 10, 2011) );
		$this->assertEquals( $Parser->object()->Sport()->id(), Configuration::General()->runningSport() );
		$this->assertEquals( $Parser->object()->avgHF(), 145 );
		$this->assertEquals( $Parser->object()->Splits()->asString(), '0.200|1:00' );
		$this->assertEquals( $Parser->object()->getArrayAltitude(), array(200, 200) );
		$this->assertEquals( $Parser->object()->getArrayDistance(), array(0.1, 0.2) );
		$this->assertEquals( $Parser->object()->getArrayHeartrate(), array(140, 150) );
		$this->assertEquals( $Parser->object()->getArrayTime(), array(30, 60) );
		$this->assertEquals( $Parser->object()->getArrayCadence(), array(80, 100) );
		$this->assertEquals( $Parser->object()->getArrayPower(), array(200, 240) );
		$this->assertEquals( $Parser->object()->getCalories(), 16 );
		$this->assertEquals( $Parser->object()->getDistance(), 0.2 );
		$this->assertEquals( $Parser->object()->getPulseAvg(), 145 );
		$this->assertEquals( $Parser->object()->getPulseMax(), 150 );
		$this->assertEquals( $Parser->object()->getTimeInSeconds(), 60 );
		$this->assertEquals( $Parser->object()->getCadence(), 90 );
		$this->assertEquals( $Parser->object()->getPower(), 220 );
		$this->assertEquals( $Parser->object()->hasArrayLatitude(), false );
		$this->assertEquals( $Parser->object()->hasArrayLongitude(), false );
	}

	public function testWrongStartTimeInTrack() {
		$XML = simplexml_load_string_utf8('
    <Activity Sport="Running">
      <Id>2011-03-06T11:21:50.000Z</Id>
      <Lap StartTime="2011-03-06T11:21:50.000Z">
        <TotalTimeSeconds>365.06</TotalTimeSeconds>
        <DistanceMeters>1000.0</DistanceMeters>
        <MaximumSpeed>3.2309999465942383</MaximumSpeed>
        <Calories>72</Calories>
        <Intensity>Active</Intensity>
        <TriggerMethod>Manual</TriggerMethod>
        <Track>
          <Trackpoint>
            <Time>2011-03-06T11:21:49.000Z</Time>
            <Position>
              <LatitudeDegrees>51.48495283909142</LatitudeDegrees>
              <LongitudeDegrees>9.156514825299382</LongitudeDegrees>
            </Position>
            <DistanceMeters>0.0</DistanceMeters>
          </Trackpoint>
          <Trackpoint>
            <Time>2011-03-06T11:21:54.000Z</Time>
            <Position>
              <LatitudeDegrees>51.485025342553854</LatitudeDegrees>
              <LongitudeDegrees>9.156750775873661</LongitudeDegrees>
            </Position>
            <DistanceMeters>9.035</DistanceMeters>
          </Trackpoint>
          <Trackpoint>
            <Time>2011-03-06T11:21:59.000Z</Time>
            <Position>
              <LatitudeDegrees>51.485025342553854</LatitudeDegrees>
              <LongitudeDegrees>9.156750775873661</LongitudeDegrees>
            </Position>
            <DistanceMeters>18.06999969482422</DistanceMeters>
          </Trackpoint>
        </Track>
      </Lap>
    </Activity>');
		$Parser = new ParserTCXSingle('', $XML);
		$Parser->parse();

		$this->assertFalse( $Parser->failed() );
		$this->assertEquals( "2011-03-06 12:21:49", LocalTime::date("Y-m-d H:i:s", $Parser->object()->getTimestamp()) );
		$this->assertEquals( array(5, 10), $Parser->object()->getArrayTime() );
		$this->assertEquals( array(0.00904, 0.01807), $Parser->object()->getArrayDistance() );
	}

}
