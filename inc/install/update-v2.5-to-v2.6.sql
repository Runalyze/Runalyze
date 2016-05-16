/* 15.04.2016 - add default sport type id to sport table*/
ALTER TABLE `runalyze_sport` ADD `default_typeid` int(10) unsigned DEFAULT NULL AFTER `main_equipmenttypeid`;

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

/* 09.05.2016 - Add primary key for weather cache table */
ALTER TABLE `runalyze_weathercache` ADD PRIMARY KEY (`geohash`,`time`);

/* 16.05.2016 - Add RaceResult table */

CREATE TABLE IF NOT EXISTS `runalyze_raceresult` (
  `official_distance` decimal(6,2) NOT NULL,
  `official_time` decimal(8,2) NOT NULL,
  `officially_measured` tinyint(1)  unsigned NOT NULL DEFAULT 0,
  `name` varchar(50) NOT NULL DEFAULT '',
  `place_total` mediumint(8) unsigned DEFAULT NULL,
  `place_gender` mediumint(8) unsigned DEFAULT NULL,
  `place_ageclass` mediumint(8) unsigned DEFAULT NULL,
  `participants_total` mediumint(8) unsigned DEFAULT NULL,
  `participants_gender` mediumint(8) unsigned DEFAULT NULL,
  `participants_ageclass` mediumint(8) unsigned DEFAULT NULL,
  `activity_id` int(10) unsigned NOT NULL,
  `accountid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`activity_id`),
  KEY  `accountid` (`accountid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `runalyze_raceresult`
ADD CONSTRAINT `runalyze_raceresult_ibfk_1` FOREIGN KEY (`accountid`) REFERENCES `runalyze_account` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `runalyze_raceresult_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `runalyze_training` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


/* 16.05.2016 - Insert existing races into runalyze_raceresults */
INSERT INTO runalyze_raceresult (`activity_id`, `accountid`, `official_distance`, `official_time`, `name`)
    SELECT `id`, `accountid`, `distance`, `s`, `comment` FROM runalyze_training
        WHERE `typeid` IN (SELECT `value` from runalyze_conf where `key`='TYPE_ID_RACE');

/* 16.05.2016 - DELETE TYPE_ID_RACE from runalyze_conf */
DELETE FROM runalyze_conf WHERE `key`='TYPE_ID_RACE';