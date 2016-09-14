<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Tool\Backup;

use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\FilenameHandler;

class FilenameHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCreatedFilenamesAreUnique()
    {
        $Handler = new FilenameHandler(42);

        $this->assertNotEquals(
            $Handler->generateInternalFilename('sql.gz'),
            $Handler->generateInternalFilename('sql.gz')
        );
    }

    public function testThatGeneratedInternalFilenameIsValid()
    {
        $Handler = new FilenameHandler(42);

        $this->assertTrue($Handler->validateInternalFilename($Handler->generateInternalFilename('sql.gz')));
    }

    public function testThatGeneratedPublicFilenameIsValid()
    {
        $Handler = new FilenameHandler(42);
        $internalFilename = $Handler->generateInternalFilename('sql.gz');

        $this->assertTrue($Handler->validatePublicFilename($Handler->generatePublicFilename($internalFilename)));
    }

    public function testThatFilenamesAreUserSpecific()
    {
        $HandlerA = new FilenameHandler(42);
        $HandlerB = new FilenameHandler(32);

        $internalFilenameA = $HandlerA->generateInternalFilename('sql.gz');
        $internalFilenameB = $HandlerB->generateInternalFilename('sql.gz');

        $this->assertNotEquals($internalFilenameA, $internalFilenameB);
        $this->assertFalse($HandlerA->validateInternalFilename($internalFilenameB));
        $this->assertFalse($HandlerB->validateInternalFilename($internalFilenameA));
    }

    public function testValidateFileExtension()
    {
        $this->assertTrue(FilenameHandler::validateFileExtension('foobar.'.FilenameHandler::JSON_FORMAT));
        $this->assertTrue(FilenameHandler::validateFileExtension('foobar.'.FilenameHandler::SQL_FORMAT));
        $this->assertFalse(FilenameHandler::validateFileExtension('foobar.txt'));
    }

    public function testValidateImportFileExtension()
    {
        $this->assertTrue(FilenameHandler::validateImportFileExtension('foobar.'.FilenameHandler::JSON_FORMAT));
        $this->assertFalse(FilenameHandler::validateImportFileExtension('foobar.'.FilenameHandler::SQL_FORMAT));
        $this->assertFalse(FilenameHandler::validateImportFileExtension('foobar.txt'));
    }
}
