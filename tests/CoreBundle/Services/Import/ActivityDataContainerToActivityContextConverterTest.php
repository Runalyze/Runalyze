<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Import;

use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Services\Import\ActivityDataContainerToActivityContextConverter;
use Runalyze\Bundle\CoreBundle\Tests\DataFixtures\AbstractFixturesAwareWebTestCase;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @group requiresDoctrine
 */
class ActivityDataContainerToActivityContextConverterTest extends AbstractFixturesAwareWebTestCase
{
    /** @var ActivityDataContainerToActivityContextConverter */
    protected $Converter;

    /** @var ActivityDataContainer */
    protected $Container;

    public function setUp()
    {
        parent::setUp();

        $this->Container = new ActivityDataContainer();
        $this->Converter = new ActivityDataContainerToActivityContextConverter(
            $this->EntityManager->getRepository('CoreBundle:Sport'),
            $this->EntityManager->getRepository('CoreBundle:Type'),
            $this->EntityManager->getRepository('CoreBundle:Equipment'),
            new ConfigurationManager(
                $this->EntityManager->getRepository('CoreBundle:Conf'),
                new TokenStorage(
                    new PreAuthenticatedToken($this->getDefaultAccount(), 'foo', 'bar')
                )
            ),
            $this->getDefaultAccount()
        );
    }

    public function testEmptyContainer()
    {
        $activityContext = $this->Converter->getContextFor($this->Container);

        $this->assertEquals($this->getDefaultAccount(), $activityContext->getAccount());

        $this->assertNull($activityContext->getSport());
        $this->assertNull($activityContext->getActivity()->getType());
        $this->assertNull($activityContext->getRoute());
        $this->assertNull($activityContext->getTrackdata());
        $this->assertNull($activityContext->getSwimdata());
        $this->assertNull($activityContext->getHrv());
        $this->assertNull($activityContext->getRaceResult());
        $this->assertEmpty($activityContext->getActivity()->getEquipment());
    }

    public function testThatSportTypeAndEquipmentCanBeGuessedByName()
    {
        $this->Container->Metadata->setSportName('Running');
        $this->Container->Metadata->setTypeName('Fartlek');
        $this->Container->Metadata->addEquipment('singlet');

        $activityContext = $this->Converter->getContextFor($this->Container);

        $this->assertEquals($this->getDefaultAccountsRunningSport(), $activityContext->getSport());
        $this->assertNotNull($activityContext->getActivity()->getType());
        $this->assertEquals('Fartlek', $activityContext->getActivity()->getType()->getName());
        $this->assertCount(1, $activityContext->getActivity()->getEquipment());
        $this->assertEquals('singlet', $activityContext->getActivity()->getEquipment()[0]->getName());
    }

    public function testThatSwimmingCanBeGuessedByName()
    {
        $this->Container->Metadata->setSportName('Swimming');
        $this->Container->Metadata->setTypeName('bathtub party');
        $this->Container->Metadata->addEquipment('rubber duck');

        $activityContext = $this->Converter->getContextFor($this->Container);

        $this->assertNotNull($activityContext->getSport());
        $this->assertEquals('Swimming', $activityContext->getActivity()->getSport()->getName());

        $this->assertNull($activityContext->getActivity()->getType());
        $this->assertEmpty($activityContext->getActivity()->getEquipment());
    }

    public function testThatLatitudesAndLongitudesAreTreatedFine()
    {
        $this->Container->ContinuousData->Latitude = [49.78, 49.79, 49.80];
        $this->Container->ContinuousData->Longitude = [7.77, 7.77, 7.77];

        $activityContext = $this->Converter->getContextFor($this->Container);

        $this->assertNotNull($activityContext->getRoute());
        $this->assertNotEmpty($activityContext->getRoute()->getGeohashes());
        $this->assertEquals($this->Container->ContinuousData->Latitude, $activityContext->getRoute()->getLatitudes());
        $this->assertEquals($this->Container->ContinuousData->Longitude, $activityContext->getRoute()->getLongitudes());
    }

    public function testThatBarometricAltitudeDataIsTreatedAsCorrected()
    {
        $this->Container->ContinuousData->IsAltitudeDataBarometric = true;
        $this->Container->ContinuousData->Altitude = [117, 117, 119, 124, 123, 120];

        $activityContext = $this->Converter->getContextFor($this->Container);

        $this->assertNotNull($activityContext->getRoute());
        $this->assertEmpty($activityContext->getRoute()->getElevationsOriginal());
        $this->assertEquals($this->Container->ContinuousData->Altitude, $activityContext->getRoute()->getElevationsCorrected());
    }

    public function testThatRRIntervalsAreConverted()
    {
        $this->Container->RRIntervals = [731, 746, 740, 752];

        $activityContext = $this->Converter->getContextFor($this->Container);

        $this->assertEquals($this->Container->RRIntervals, $activityContext->getHrv()->getData());
    }

    public function testDistanceRounding()
    {
        $this->Container->ContinuousData->Distance = [0.12345678, 0.98765432];

        $activityContext = $this->Converter->getContextFor($this->Container);

        $this->assertEquals([0.12346, 0.98765], $activityContext->getTrackdata()->getDistance());
    }
}
