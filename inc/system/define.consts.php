<?php
/*
 * All definitions of CONST variables have to be done here
 * otherwise the Autoloader will not work correct
 */

/**
 * Current version of Runalyze
 * @var string
 */
define('RUNALYZE_VERSION', '2.5.0-dev');

/**
 * Maximum value for integers
 * @var int 
 */
define('INFINITY', PHP_INT_MAX);

/**
 * Number of seconds for a complete day
 * @var int 
 */
define('DAY_IN_S', 86400);

/**
 * Current year
 * @var int 
 */
define('YEAR', date("Y"));

/**
 * Default length for cutting strings
 * @var int 
 */
define('CUT_LENGTH', 29);

/**
 * New line for echoing text
 * @var string
 */
define('NL', PHP_EOL);

/**
 * No break space
 * @var string
 */
define('NBSP', '&nbsp;');

/**
 * HTML-tag: break
 * @var string
 */
define('BR', '<br>');

/**
 * Tag-ID for the whole databrowser
 * @const DATA_BROWSER_ID
 */
define('DATA_BROWSER_ID', 'data-browser-inner');

/**
 * Tag-ID for the public databrowser
 * @const DATA_BROWSER_SHARED_ID
 */
define('DATA_BROWSER_SHARED_ID', 'publicList');

/**
 * Tag-ID for the search
 * @const DATA_BROWSER_SEARCH_ID
 */
define('DATA_BROWSER_SEARCH_ID', 'search');

/**
 * Tag-ID for the resultbrowser of the search
 * @const DATA_BROWSER_SEARCHRESULT_ID
 */
define('DATA_BROWSER_SEARCHRESULT_ID', 'searchResult');
