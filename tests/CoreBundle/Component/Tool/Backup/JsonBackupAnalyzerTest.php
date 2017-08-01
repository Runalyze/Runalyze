<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Tool\Backup;

use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\JsonBackupAnalyzer;

class JsonBackupAnalyzerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    const CURRENT_VERSION = '4.2.0';

    /** @var string */
    protected $Base;

    public function setUp()
    {
        $this->Base = __DIR__.'/../../../../testfiles/backup/';
    }

    public function testVersionExtraction()
    {
        $versions = [
            '' => '',
            'no version' => '',
            '.0.1' => '',
            '0' => '',
            '1.0' => '1.0',
            '2.34' => '2.34',
            '2.5-dev' => '2.5',
            '2.6rc' => '2.6',
            '3.0.0-alpha' => '3.0',
            '3.0.1' => '3.0',
            '45.6-beta' => '45.6'
        ];

        foreach ($versions as $string => $version) {
            $this->assertEquals($version, JsonBackupAnalyzer::extractMajorAndMinorVersion($string));
        }
    }

    public function testVersionExtractionForUnknownVersion()
    {
        $analyzer = new JsonBackupAnalyzer($this->Base.'no-version.json.gz', self::CURRENT_VERSION);

        $this->assertFalse($analyzer->fileIsOkay());
        $this->assertFalse($analyzer->versionIsOkay());
        $this->assertEquals('unknown', $analyzer->fileVersion());
    }

    public function testVersionExtractionForWrongVersion()
    {
        $analyzer = new JsonBackupAnalyzer($this->Base.'wrong-version.json.gz', self::CURRENT_VERSION);

        $this->assertFalse($analyzer->fileIsOkay());
        $this->assertFalse($analyzer->versionIsOkay());
        $this->assertEquals('v1.0-alpha', $analyzer->fileVersion());
    }

    public function testVersionExtractionForDefaultEmpty()
    {
        $analyzer = new JsonBackupAnalyzer($this->Base.'default-empty.json.gz', self::CURRENT_VERSION);

        $this->assertTrue($analyzer->fileIsOkay());
        $this->assertTrue($analyzer->versionIsOkay());
    }

    public function testVersionExtractionForDefaultInsert()
    {
        $analyzer = new JsonBackupAnalyzer($this->Base.'default-insert.json.gz', self::CURRENT_VERSION);

        $this->assertTrue($analyzer->fileIsOkay());
        $this->assertTrue($analyzer->versionIsOkay());
    }

    public function testVersionExtractionForDefaultUpdate()
    {
        $analyzer = new JsonBackupAnalyzer($this->Base.'default-update.json.gz', self::CURRENT_VERSION);

        $this->assertTrue($analyzer->fileIsOkay());
        $this->assertTrue($analyzer->versionIsOkay());
    }

    public function testVersionExtractionForWithEquipment()
    {
        $analyzer = new JsonBackupAnalyzer($this->Base.'with-equipment.json.gz', self::CURRENT_VERSION);

        $this->assertTrue($analyzer->fileIsOkay());
        $this->assertTrue($analyzer->versionIsOkay());
    }
}
