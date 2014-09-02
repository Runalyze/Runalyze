<?php
include_once FRONTEND_PATH.'/lib/phpfastcache/phpfastcache.php';
/**
 * Class: Cache - Wrapcacher for PHPFastCache
 * @author Michael Pohl
 * @package Runalyze\System
 */
class Cache {
	/**
	 * Force log file to be written
	 * @var boolean
	 */
	private static $FORCE_LOG_FILE = false;

	/**
	 * Boolean flag: Has the footer been sent?
	 * @var bool
	 */
	public $footer_sent = false;
        
        private $cache;

	/**
	 * Prohibit creating an object from outside
	 */
	private function __construct() {
            phpFastCache::setup("storage","auto");
        }

	/**
	 * Get Cache
	 */
	static public function get() {
            
        }
        
        /**
         * Set Cache
         */
        static public function set() {
            
        }


}
