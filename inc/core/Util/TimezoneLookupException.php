<?php
/**
 * This file contains class::TimezoneLookupException
 * @package Runalyze\Util
 */
namespace Runalyze\Util;

/**
 * Exception for TimezoneLookup
 *
 * @author Hannes Christiansen
 * @package Runalyze\Util
 */
class TimezoneLookupException extends \Exception
{
    /** @var string */
    const TIMEZONE_DB_MISSING = 'Timezone database is missing.';

    /** @var string */
    const SQLITE3_MISSING = 'SQLite3 is not available.';

    /** @var string */
    const SPATIALITE_MISSING = 'SQLite3 extension \'spatialite\' can\'t be loaded.';
}