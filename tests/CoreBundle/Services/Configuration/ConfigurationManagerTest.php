<?php

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Conf;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ConfigurationManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyList()
    {
        $repository = $this->getConfRepositoryMock([]);
        $manager = new ConfigurationManager($repository, new TokenStorage());
        $list = $manager->getList(new Account());

        $this->assertEquals('metric', $list->get('general.DISTANCE_UNIT_SYSTEM'));
    }

    public function testSimpleList()
    {
        $existingConf = new Conf();
        $existingConf->setCategory('general');
        $existingConf->setKey('DISTANCE_UNIT_SYSTEM');
        $existingConf->setValue('imperial');

        $repository = $this->getConfRepositoryMock([$existingConf]);
        $manager = new ConfigurationManager($repository, new TokenStorage());
        $list = $manager->getList(new Account());

        $this->assertEquals('imperial', $list->get('general.DISTANCE_UNIT_SYSTEM'));
    }

    /**
     * @param Conf[] $existingData
     * @return ConfRepository
     */
    protected function getConfRepositoryMock(array $existingData = [])
    {
        /** @var ConfRepository */
        $repository = $this
            ->getMockBuilder(ConfRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(array('findByAccount'))
            ->getMock();

        $repository
            ->expects($this->once())
            ->method('findByAccount')
            ->will($this->returnValue($existingData));

        return $repository;
    }
}
