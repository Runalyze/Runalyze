<?php
/**
 * This file contains class::TimezoneLookupException
 * @package Runalyze\Util
 */
namespace Runalyze\Util;

/**
 * @deprecated since v4.3
 * @see \Runalyze\Bundle\CoreBundle\Services\Import\TimezoneLookupException
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
