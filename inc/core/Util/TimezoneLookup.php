<?php
/**
 * This file contains class::TimezoneLookup
 * @package Runalyze\Util
 */
namespace Runalyze\Util;

/**
 * TimezoneLookup
 *
 * @author Michael Pohl
 * @package Runalyze\Util
 */
class TimezoneLookup
{
    /**
     * SQLite available?
     * @var bool
     */
    protected $IsSpatialSqliteAvailable = false;

    /**
     * Path to SQLite TZ World database
     * @var string
     */
    protected $SQLiteTzWorldDatabase;

    /**
     * Name of spatialite extension
     * @var string depends on os, linux default: libspatialite.so.5
     */
    protected $SpatialiteExtensionName;

    /**
     * SQLite Connection
     * @var \SQLite3
     */
    protected $Db = null;

    /**
     * Construct object
     * @param bool $silenceExceptions
     * @param string|bool $pathToDatabase absolute path to database, can be false to use default path
     * @param string $extensionName
     * @throws \Runalyze\Util\TimezoneLookupException
     */
    public function __construct($silenceExceptions = true, $pathToDatabase = false, $extensionName = SQLITE_MOD_SPATIALITE) {
        try {
            $this->SQLiteTzWorldDatabase = $pathToDatabase ? $pathToDatabase : DATA_DIRECTORY.'/timezone.sqlite';
            $this->SpatialiteExtensionName = $extensionName;

            $this->connectSQLite();
            $this->checkSpatialExtension();
        } catch (TimezoneLookupException $e) {
            if ($silenceExceptions) {
                $this->IsSpatialSqliteAvailable = false;
            } else {
                throw $e;
            }
        }
    }

    /**
     * @return bool
     */
    public function isPossible()
    {
        return (null !== $this->Db) && $this->IsSpatialSqliteAvailable;
    }

    /**
     * Get timezoneid for coordinate
     * @param float $longitude
     * @param float $latitude
     * @return null|string
     */
    public function getTimezoneForCoordinate($longitude, $latitude)
    {
        if ($this->isPossible() && is_numeric($longitude) && is_numeric($latitude)) {
            $query = $this->Db->query('SELECT `tzid` FROM `tz_world` WHERE ST_Contains(`geometry`, MakePoint('.$longitude.', '.$latitude.'))');

            if (!is_bool($query)) {
                $result = $query->fetchArray();

                if ($result['tzid']) {
                    return $result['tzid'];
                }
            }
        }

        return null;
    }

    /**
     * Check if Spatial extension for SQLite is available
     * @throws \Runalyze\Util\TimezoneLookupException
     */
    protected function checkSpatialExtension()
    {
        $query = $this->Db->query("SELECT spatialite_version()");

        if ($query) {
            $this->IsSpatialSqliteAvailable = true;
        } else {
            throw new TimezoneLookupException(TimezoneLookupException::SPATIALITE_MISSING);
        }
    }

    /**
     * Try to connect to SQLite
     * @throws \Runalyze\Util\TimezoneLookupException
     */
    protected function connectSQLite()
    {
        if (!file_exists($this->SQLiteTzWorldDatabase)) {
            throw new TimezoneLookupException(TimezoneLookupException::TIMEZONE_DB_MISSING);
        }

        if (!class_exists('SQLite3')) {
            throw new TimezoneLookupException(TimezoneLookupException::SQLITE3_MISSING);
        }

        // Cannot work with SqlitePdo - Cannot load extension
        $this->Db = new \SQLite3($this->SQLiteTzWorldDatabase);

        try {
            if (!@$this->Db->loadExtension($this->SpatialiteExtensionName)) {
                throw new TimezoneLookupException(TimezoneLookupException::SPATIALITE_MISSING);
            }
        } catch (\Exception $e) {
            throw new TimezoneLookupException(TimezoneLookupException::SPATIALITE_MISSING);
        }
    }
}
