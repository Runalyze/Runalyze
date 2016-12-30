<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Twig;

use Runalyze\Bundle\CoreBundle\Twig\UtilityExtension;

class UtilityExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var UtilityExtension */
    protected $Utility;

    public function setUp()
    {
        $this->Utility = new UtilityExtension();
    }

    public function testDuration()
    {
        $this->assertEquals('0:09.58', $this->Utility->duration(9.579, 2, '.'));
        $this->assertEquals('2:41,03', $this->Utility->duration(161.03, 2, ','));
        $this->assertEquals('0:13', $this->Utility->duration(13));
        $this->assertEquals('22:07', $this->Utility->duration(1327));
        $this->assertEquals('59:59', $this->Utility->duration(3599));
        $this->assertEquals('1:00:01', $this->Utility->duration(3601));
        $this->assertEquals('3:35:47', $this->Utility->duration(12947));
        $this->assertEquals('1d 01:03:05', $this->Utility->duration(90185));
    }

    public function testFilesizeAsString()
    {
        $this->assertEquals('3 B', $this->Utility->filesizeAsString(3.14));
        $this->assertEquals('1.00 kB', $this->Utility->filesizeAsString(1024));
        $this->assertEquals('2.35 MB', $this->Utility->filesizeAsString(2.345 * pow(1024, 2)));
        $this->assertEquals('1.20 GB', $this->Utility->filesizeAsString(1.2 * pow(1024, 3)));
        $this->assertEquals('271.30 GB', $this->Utility->filesizeAsString(271.3 * pow(1024, 3)));
        $this->assertEquals('8.02 TB', $this->Utility->filesizeAsString(8.02 * pow(1024, 4)));
        $this->assertEquals('6.33 PB', $this->Utility->filesizeAsString(6.33 * pow(1024, 5)));
        $this->assertEquals('7.41 EB', $this->Utility->filesizeAsString(7.41 * pow(1024, 6)));
        $this->assertEquals('5.90 ZB', $this->Utility->filesizeAsString(5.90 * pow(1024, 7)));
        $this->assertEquals('1.00 YB', $this->Utility->filesizeAsString(1.00 * pow(1024, 8)));
    }
}
