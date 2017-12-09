<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

class TimezoneLookup
{
    /** @var string sqlite-file with time zone database */
    protected $PathToTimeZoneDatabase;

    /** @var string depends on os, linux default: libspatialite.so.5 */
    protected $SpatialiteExtensionName;

    /** @var \SQLite3|null|false */
    protected $SQLite = null;

    /** @var bool */
    protected $SilentExceptions = false;

    /**
     * @param string $pathToDatabase absolute path to database
     * @param string $extensionName
     * @param bool $silentExceptions
     */
    public function __construct($pathToDatabase, $extensionName, $silentExceptions = false)
    {
        $this->PathToTimeZoneDatabase = $pathToDatabase;
        $this->SpatialiteExtensionName = $extensionName;
        $this->SilentExceptions = $silentExceptions;
    }

    /**
     * @param bool $flag
     */
    public function silentExceptions($flag = true)
    {
        $this->SilentExceptions = $flag;
    }

    /**
     * @return bool
     * @throws TimezoneLookupException
     */
    public function isPossible()
    {
        try {
            $sqLite = $this->getSQLite();

            if ($sqLite instanceof \SQLite3) {
                return true;
            }
        } catch (TimezoneLookupException $e) {
            if (!$this->SilentExceptions) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * @param float $longitude
     * @param float $latitude
     * @return null|string
     */
    public function getTimezoneForCoordinate($longitude, $latitude)
    {
        if ($this->isPossible() && is_numeric($longitude) && is_numeric($latitude)) {
            $query = $this->SQLite->query('SELECT `tzid` FROM `tz_world` WHERE ST_Contains(`geometry`, MakePoint('.$longitude.', '.$latitude.'))');

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
     * @return false|null|\SQLite3
     */
    protected function getSQLite()
    {
        if (null === $this->SQLite) {
            $this->tryToConnectToSQLite();
            $this->checkSpatialiteExtension();
        }

        return $this->SQLite;
    }

    protected function tryToConnectToSQLite()
    {
        if (!file_exists($this->PathToTimeZoneDatabase)) {
            throw new TimezoneLookupException(TimezoneLookupException::TIMEZONE_DB_NOT_AVAILABLE);
        }

        if (!class_exists('SQLite3')) {
            throw new TimezoneLookupException(TimezoneLookupException::SQLITE3_NOT_AVAILABLE);
        }

        $this->SQLite = new \SQLite3($this->PathToTimeZoneDatabase);

        try {
            if (!@$this->SQLite->loadExtension($this->SpatialiteExtensionName)) {
                throw new TimezoneLookupException(TimezoneLookupException::SPATIALITE_NOT_AVAILABLE);
            }
        } catch (\Exception $e) {
            throw new TimezoneLookupException(TimezoneLookupException::SPATIALITE_NOT_AVAILABLE);
        }
    }

    protected function checkSpatialiteExtension()
    {
        if (!($this->SQLite instanceof \SQLite3)) {
            $this->SQLite = false;

            return;
        }

        $query = $this->SQLite->query("SELECT spatialite_version()");

        if (!$query) {
            $this->SQLite = false;

            throw new TimezoneLookupException(TimezoneLookupException::SPATIALITE_NOT_AVAILABLE);
        }
    }
}
