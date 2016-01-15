<?php
/**
 * This file contains class::DuplicateFinder
 * @package Runalyze\Import
 */


class DuplicateFinder {
    
    protected $PDO;


    public function __construct(PDOforRunalyze $PDO) {
	$this->PDO = $PDO;
    }
    
    public function checkForDuplicate($activityId) {
	return $this->PDO->query('SELECT 1 FROM `'.PREFIX.'training` WHERE activity_id = "'.$activityId.'" LIMIT 1')->fetch();
    }
}