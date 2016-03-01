<?php
/**
 * This file contains class::TimezoneLookup
 * @package Runalyze\Data
 */
namespace Runalyze\Data;

/**
 * TimezoneLookup
 * 
 * @author Michael Pohl
 * @package Runalyze\Data
 */
class TimezoneLookup {
    
	/**
	 * SQLite available?
	 */
	protected $SpatialSqliteAvailable = false;
	
	/**
	 * Path to SQLite TZ World database?
	 */
	protected $SQLiteTzWorldDatabase = 'data/timezone.sqlite';
	
	/*
	 * SQLite Connection
	 */
	protected $db;
    
	public function __construct() {
	    $this->connectSQLite();
	    $this->checkSpatialExtension();
	}

	/*
	 * Get timezoneid for coordinate
	 * @param int $longitude
	 * @param int $latitude
	 * @return string
	 */
	public function getTimezoneForCoordinate($longitude, $latitude) {
	    if(!$this->SpatialSqliteAvailable)
		return;
	    $query = $this->db->query("SELECT tzid FROM tz_world WHERE ST_Contains(geometry,MakePoint(".$longitude.", ".$latitude."))");
	    if(!is_bool($query)) {
		$result = $query->fetchArray();
		if($result['tzid']) {
		    return $result['tzid'];
		}
	    }
	}

	/*
	 * Check if Spatial extension for SQLite is available
	 */
	private function checkSpatialExtension() {
	    $query = $this->db->query("SELECT spatialite_version()");
	    if($query) {
		$this->SpatialSqliteAvailable = true;
	    }
	}

	private function connectSQLite() {
	    //Cannot work with SqlitePdo - Cannot load extension
	    $this->db = new \SQLite3($this->SQLiteTzWorldDatabase);
	    $this->db->loadExtension('libspatialite.so.5');

	}
    
    
}