/* 15.04.2016 - add default sport type id to sport table*/
ALTER TABLE `runalyze_sport` ADD `default_typeid` int(10) unsigned DEFAULT NULL AFTER `main_equipmenttypeid`, ADD `race_typeid` int(10) unsigned DEFAULT NULL AFTER `default_typeid`;

/* 24.04.2016 - add training effect, add RPE to activity (training) table */
ALTER TABLE `runalyze_training` ADD `fit_training_effect` decimal(2,1) unsigned DEFAULT NULL AFTER `fit_hrv_analysis`, ADD `rpe` tinyint(2) unsigned DEFAULT NULL AFTER `jd_intensity`; 

/* 02.05.2016 - Add Weather Cache table */
CREATE TABLE IF NOT EXISTS `runalyze_weathercache` (
  `time` int(11) NOT NULL DEFAULT '0',
  `geohash` char(5) DEFAULT NULL,
  `temperature` tinyint(4) DEFAULT NULL,
  `wind_speed` tinyint(3) unsigned DEFAULT NULL,
  `wind_deg` smallint(3) unsigned DEFAULT NULL,
  `humidity` tinyint(3) unsigned DEFAULT NULL,
  `pressure` smallint(4) unsigned DEFAULT NULL,
  `weatherid` smallint(6) NOT NULL DEFAULT '1',
  `weather_source` tinyint(2) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 05.05.2016 - Add RaceResult table */

CREATE TABLE IF NOT EXISTS `runalyze_raceresult` (
  `official_distance` decimal(6,2) NOT NULL,
  `official_time` decimal(8,2) NOT NULL,
  `officially_measured` tinyint(1)  unsigned NOT NULL DEFAULT 0,
  `name` varchar(50) NOT NULL DEFAULT '',
  `place_total` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `place_gender` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `place_ageclass` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `participants_total` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `participants_gender` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `participants_ageclass` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `activity_id` int(10) unsigned NOT NULL,
  `accountid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`activity_id`),
  KEY  `accountid` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* 05.05.2016 - Insert existing races into runalyze_raceresults */
INSERT INTO runalyze_raceresult (`activity_id`, `accountid`, `official_distance`, `official_time`, `name`)  
    SELECT `id`, `accountid`, `distance`, `s`, `comment` FROM runalyze_training 
        WHERE `typeid` IN (SELECT `value` from runalyze_conf where `key`='TYPE_ID_RACE');

/* 05.05.2016 - Copy existing race types in sport table */
UPDATE runalyze_sport s SET s.race_typeid=(SELECT value FROM runalyze_conf c LEFT JOIN runalyze_type t ON t.id=c.value WHERE c.`key`='TYPE_ID_RACE' AND t.sportid=s.id);

/* 05.05.2016 - DELETE TYPE_ID_RACE from runalyze_conf */
DELETE FROM runalyze_conf WHERE `key`='TYPE_ID_RACE';