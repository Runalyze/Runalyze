<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Training;

class TrainingTest extends \PHPUnit_Framework_TestCase
{
    /** @var Training */
    protected $Activity;

    public function setUp()
    {
        $this->Activity = new Training();
    }

    public function testCloningSplits()
    {
        $oldSplits = $this->Activity->getSplits();
        $this->Activity->setSplitsToClone();

        $this->assertNotSame($this->Activity->getSplits(), $oldSplits);
    }
}
