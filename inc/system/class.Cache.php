<?php
/**
 * Class: Cache - Wrapper for PHPFastCache
 * @author Michael Pohl
 * @package Runalyze\System
 */
class Cache {

	/**
	 * Boolean flag: Cache enabled?
	 * @var bool
	 */
	public $footer_sent = true;
        
        public static $cache;

	/**
	 * Prohibit creating an object from outside
	 */
	public function __construct() {
            phpFastCache::setup("storage", "files");
            self::$cache = new phpFastCache();
        }

	/**
	 * Get Cache
	 */
	static public function set($keyword, $data, $time, $nousercache = 0) {
            if($nousercache == 0) { 
                $key = $keyword.SessionAccountHandler::getId();
                self::$cache->set($key, $data, $time);
            } else {
                self::$cache->set($keyword,$data, $time);
            }
        }
        
        /**
         * Set Cache
         */
        static public function get($keyword, $nousercache = 0) {
           if($nousercache == 0) { 
               return self::$cache->get($keyword.SessionAccountHandler::getId());
               } else {
               return self::$cache->get($keyword);
            }            
        }
        
        /**
         * Delete from cache
         */
        static public function delete($keyword, $nousercache = 0) {
            if($nousercache == 0) { 
               return self::$cache->delete($keyword.SessionAccountHandler::getId());
               } else {
               return self::$cache->delete($keyword);
            }  
        }
        
        /**
         * Clean up all cache
         */
        static public function clean() {
            self::$cache->clean();
        }
        
        /**
         * is existing?
         */
        static public function is($keyword, $nousercache = 0) {
            if($nousercache == 0) { 
               return self::$cache->isExisting($keyword.SessionAccountHandler::getId());
               } else {
               return self::$cache->isExisting($keyword);
            }  
        }
}
