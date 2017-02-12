<?php

namespace Runalyze\Tests\Sports\Running\Prognosis;

use Runalyze\Sports\Running\Prognosis\Bock;

class BockTest extends \PHPUnit_Framework_TestCase
{
    /** @var Bock */
	protected $Bock;

	protected function setUp()
    {
		$this->Bock = new Bock();
	}

	public function testWithoutReferenceTime()
    {
        $this->assertFalse($this->Bock->areValuesValid());
    }

    /**
     * Remember: Formulas used in Bock's generator do not match to his tables
     */
    public function testSetFromResultsAndInSeconds()
    {
        $this->Bock->setFromResults(10.0, 30 * 60 + 0, 21.1, 65 * 60);
        $this->assertEquals(  8 * 60 + 37, $this->Bock->getSeconds(3.0), '', 1);
        $this->assertEquals( 14 * 60 + 37, $this->Bock->getSeconds(5.0), '', 1);
        $this->assertEquals( 30 * 60 +  0, $this->Bock->getSeconds(10.0), '', 1);
        $this->assertEquals( 65 * 60 +  0, $this->Bock->getSeconds(21.1), '', 1);
        $this->assertEquals(133 * 60 + 14, $this->Bock->getSeconds(42.2), '', 1);

        $this->Bock->setFromResults(10.0, 30 * 60 + 0, 21.1, 70 * 60);
        $this->assertEquals(  7 * 60 + 39, $this->Bock->getSeconds(3.0), '', 1);
        $this->assertEquals( 13 * 60 + 40, $this->Bock->getSeconds(5.0), '', 1);
        $this->assertEquals( 30 * 60 +  0, $this->Bock->getSeconds(10.0), '', 1);
        $this->assertEquals( 70 * 60 +  0, $this->Bock->getSeconds(21.1), '', 1);
        $this->assertEquals(153 * 60 + 42, $this->Bock->getSeconds(42.2), '', 1);

        $this->Bock->setFromResults(10.0, 40 * 60 + 0, 21.1, 90 * 60);
        $this->assertEquals( 10 * 60 + 49, $this->Bock->getSeconds(3.0), '', 1);
        $this->assertEquals( 18 * 60 + 51, $this->Bock->getSeconds(5.0), '', 1);
        $this->assertEquals( 40 * 60 +  0, $this->Bock->getSeconds(10.0), '', 1);
        $this->assertEquals( 90 * 60 +  0, $this->Bock->getSeconds(21.1), '', 1);
        $this->assertEquals(191 * 60 +  4, $this->Bock->getSeconds(42.2), '', 1);
    }
}
