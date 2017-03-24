<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;

class ConfRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var ConfRepository */
    protected $ConfRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->ConfRepository = $this->EntityManager->getRepository('CoreBundle:Conf');
    }

    public function testEmptyDatabase()
    {
        $account = $this->getEmptyAccount();

        $this->assertEquals([], $this->ConfRepository->findByAccount($account));
        $this->assertNull($this->ConfRepository->findByAccountAndKey($account, 'SOME_RANDOM_KEY'));
    }

    public function testInsertingAndUpdatingKey()
    {
        $account = $this->getEmptyAccount();

        $conf = $this->ConfRepository->updateOrInsert($account, 'cat', 'SOME_KEY', 'foo');

        $this->assertEquals([$conf], $this->ConfRepository->findByAccount($account));
        $this->assertEquals($conf, $this->ConfRepository->findByAccountAndKey($account, 'SOME_KEY'));
        $this->assertEquals('foo', $conf->getValue());

        $this->ConfRepository->updateOrInsert($account, 'cat', 'SOME_KEY', 'bar');

        $this->assertEquals(1, count($this->ConfRepository->findByAccount($account)));
        $this->assertEquals('bar', $this->ConfRepository->findByAccountAndKey($account, 'SOME_KEY')->getValue());
    }
}
