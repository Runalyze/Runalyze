<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

class TimezoneLookupException extends \Exception
{
    /** @var string */
    const TIMEZONE_DB_NOT_AVAILABLE = 'Timezone database is missing.';

    /** @var string */
    const SQLITE3_NOT_AVAILABLE = 'SQLite3 is not available.';

    /** @var string */
    const SPATIALITE_NOT_AVAILABLE = 'SQLite3 extension \'spatialite\' can\'t be loaded.';
}
