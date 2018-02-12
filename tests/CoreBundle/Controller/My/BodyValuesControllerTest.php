<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Tests\DataFixtures\AbstractFixturesAwareWebTestCase;

/**
 * @group requiresKernel
 * @group requiresClient
 */
class BodyValuesControllerTest extends AbstractFixturesAwareWebTestCase
{
    /** @var Account */
    protected $Account;

    protected function setUp()
    {
        parent::setUp();

        $this->Account = $this->getDefaultAccount();
    }

    public function testAddAction()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/my/body-values/add');

        $this->assertStatusCode(200, $client);
    }

    public function testTableAction()
    {
        $client = $this->makeClient(true);
        $client->request('GET', '/my/body-values/table');

        $this->assertStatusCode(200, $client);
    }

}
