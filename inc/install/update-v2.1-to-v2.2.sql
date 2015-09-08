/* 08.07.2015 - add hrv table */
CREATE TABLE IF NOT EXISTS `runalyze_hrv` ( `accountid` int(10) unsigned NOT NULL, `activityid` int(10) unsigned NOT NULL, `data` longtext ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `runalyze_hrv` ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

/* 16.07.2015 - on branch feature/removePaceArray - remove pace from db */
ALTER TABLE `runalyze_trackdata` DROP `pace`;

/* 01.08.2015 - add swim table */

CREATE TABLE IF NOT EXISTS `runalyze_swimdata` (
  `accountid` int(10) unsigned NOT NULL,
  `activityid` int(10) unsigned NOT NULL,
  `stroke` longtext,
  `stroketype` longtext,
  `pool_length` smallint(5) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `runalyze_swimdata`
 ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);

ALTER TABLE `runalyze_training` ADD `total_strokes` smallint(5) unsigned NOT NULL DEFAULT '0', ADD `swolf` tinyint(3) unsigned NOT NULL DEFAULT '0' AFTER `power`;

/* 04.09.2015 - add recovery advisor for fit files */
ALTER TABLE `runalyze_training` ADD `fit_vdot_estimate` TINYINT(2) UNSIGNED NOT NULL DEFAULT '0' AFTER `use_vdot`, ADD `fit_recovery_time` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `fit_vdot_estimate`, ADD `fit_hrv_analysis` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `fit_recovery_time`;
