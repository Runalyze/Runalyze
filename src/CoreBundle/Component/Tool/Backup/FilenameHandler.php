<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Backup;

class FilenameHandler
{
    /** @var string */
    const FILENAME_PREFIX = 'runalyze-backup';

    /** @var string */
    const JSON_FORMAT = 'json.gz';

    /** @var string */
    const SQL_FORMAT = 'sql.gz';

    /** @var int */
    protected $AccountId;

    /** @var string */
    protected $RunalyzeVersion = 'x';

    /**
     * @param int $accountId
     *
     * @TODO realize as service using DI
     */
    public function __construct($accountId)
    {
        $this->AccountId = (int)$accountId;
    }

    /**
     * @param string $version
     */
    public function setRunalyzeVersion($version)
    {
        $this->RunalyzeVersion = $version;
    }

    /**
     * @param string $extension
     * @return string
     */
    public function generateInternalFilename($extension)
    {
        return sprintf('%s-%s-%s-%s-%s.%s',
            $this->AccountId,
            self::FILENAME_PREFIX,
            date('Ymd-Hi'),
            $this->RunalyzeVersion,
            substr(uniqid(rand()), -4),
            $extension
        );
    }

    /**
     * @param string $extension
     * @return bool
     */
    public function isValidImportExtension($extension)
    {
        return self::JSON_FORMAT === $extension;
    }

    /**
     * @param string $extension
     * @return bool
     */
    public function isValidExportExtension($extension)
    {
        return in_array($extension, [self::JSON_FORMAT, self::SQL_FORMAT]);
    }

    /**
     * @param string $internalFilename
     * @return bool
     */
    public function validateInternalFilename($internalFilename)
    {
        return $this->validateInternalPrefix($internalFilename) && self::validateFileExtension($internalFilename);
    }

    /**
     * @param string $internalFilename
     * @return bool
     */
    protected function validateInternalPrefix($internalFilename)
    {
        $expectedPrefix = $this->AccountId.'-'.self::FILENAME_PREFIX;

        return substr($internalFilename, 0, strlen($expectedPrefix)) === $expectedPrefix;
    }

    /**
     * @param string $completeFilename
     * @return bool
     */
    static public function validateFileExtension($completeFilename)
    {
        return (
            substr($completeFilename, -strlen(self::JSON_FORMAT)) === self::JSON_FORMAT ||
            substr($completeFilename, -strlen(self::SQL_FORMAT)) === self::SQL_FORMAT
        );
    }

    /**
     * @param string $completeFilename
     * @return bool
     */
    static public function validateImportFileExtension($completeFilename)
    {
        return substr($completeFilename, -strlen(self::JSON_FORMAT)) === self::JSON_FORMAT;
    }

    /**
     * @param string $internalFilename
     * @return string
     */
    public function generatePublicFilename($internalFilename)
    {
        return substr($internalFilename, strlen($this->AccountId.'-'.self::FILENAME_PREFIX));
    }

    /**
     * @param string $publicFilename
     * @return bool
     */
    public function validatePublicFilename($publicFilename)
    {
        return $this->validateInternalFilename($this->transformPublicToInternalFilename($publicFilename));
    }

    /**
     * @param string $publicFilename
     * @return string
     */
    public function transformPublicToInternalFilename($publicFilename)
    {
        return $this->AccountId.'-'.self::FILENAME_PREFIX.'-'.$publicFilename;
    }
}
