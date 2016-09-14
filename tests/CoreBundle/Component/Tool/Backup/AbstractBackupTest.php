<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Tool\Backup;

use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\AbstractBackup;

class AbstractBackupTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    const TESTFILE = '/../../../../../data/backup-tool/backup/test.json.gz';

    /** @var AbstractBackup */
    protected $Backup;

    public function setUp()
    {
        $mockBuilder = $this->getMockBuilder(AbstractBackup::class);
        $mockBuilder->setConstructorArgs([__DIR__.self::TESTFILE, 1, \DB::getInstance(), 'runalyze_', '3.0.0']);
        $this->Backup = $mockBuilder->getMockForAbstractClass();
    }

    public function tearDown()
    {
        if (file_exists(__DIR__.self::TESTFILE)) {
            unlink(__DIR__.self::TESTFILE);
        }
    }

    public function testThatBackupQueriesDoWork()
    {
        $this->Backup->run();
    }
}
