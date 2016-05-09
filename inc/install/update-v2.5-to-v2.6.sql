/* 15.04.2016 - add default sport type id to sport table*/
ALTER TABLE `runalyze_sport` ADD `default_typeid` int(10) unsigned DEFAULT NULL AFTER `main_equipmenttypeid`;

/* 24.04.2016 - add training effect & add RPE to activity (training) table*/
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
