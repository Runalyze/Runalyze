<?php
/**
 * Class: Cache - Wrapper for PHPFastCache
 * @author Michael Pohl
 * @package Runalyze\System
 */

class Cache {
	/**
	 * Path for cache, relative to runalyze root
	 * @var string
	 */
	const PATH = 'data';

	/**
	 * Last cache clean date
	 * @var int
	*/
	private static $LASTCLEAN = null;

	/**
	 * Boolean flag: Cache enabled?
	 * @var bool
	 */
	public $footer_sent = true;

	/** @var \phpFastCache */
    public static $cache;

	/**
	 * Prohibit creating an object from outside
	 */
	public function __construct() {
		phpFastCache::setup("storage", "files");
		phpFastCache::setup("path", FRONTEND_PATH."../".self::PATH);
		phpFastCache::setup("securityKey", "cache");
		self::$cache = new phpFastCache;
	}

	/**
	 * Get Cache
	 */
	public static function set($keyword, $data, $time, $nousercache = 0) {
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
	public static function get($keyword, $nousercache = 0) {
		if ($nousercache == 0) {
			$key = $keyword . SessionAccountHandler::getId();
			$cachedobj = self::$cache->getinfo($key);
			$lastcacheclean = self::$LASTCLEAN;
			if ($lastcacheclean === null) {
				$lastcacheclean = self::$cache->get('LASTCLEAN' . SessionAccountHandler::getId());
				$lastcacheclean = $lastcacheclean ? : 0;
				self::$LASTCLEAN = $lastcacheclean;
			}
			if (isset($cachedobj['write_time']) && $cachedobj['write_time'] > $lastcacheclean) {
				return $cachedobj['value'];
			} else {
				return null;
			}
		} else {
			return self::$cache->get($keyword);
		}
	}

	/**
	 * Delete from cache
	 */
	public static function delete($keyword, $nousercache = 0) {
		if ($nousercache == 0) { 
			return self::$cache->delete($keyword.SessionAccountHandler::getId());
		} else {
			return self::$cache->delete($keyword);
		}
	}

	/**
	 * Clean up all cache
	 */
	public static function clean() {
		self::$LASTCLEAN = time();

		if (SessionAccountHandler::getId() === null) {
			self::$cache->clean();
		} else {
			self::$cache->set('LASTCLEAN' . SessionAccountHandler::getId(), self::$LASTCLEAN);
		}
	}

	/**
	 * is existing?
	 */
	public static function is($keyword, $nousercache = 0) {
		if ($nousercache == 0) { 
			return self::$cache->isExisting($keyword.SessionAccountHandler::getId());
		} else {
			return self::$cache->isExisting($keyword);
		}
	}
}
