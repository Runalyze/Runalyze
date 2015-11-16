/* 18.10.2015 - drop 'types' flag for sport */
ALTER TABLE `runalyze_sport` DROP `types`;

/* 08.11.2015 -  add tables for tags */
CREATE TABLE IF NOT EXISTS `runalyze_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY  `tagAccount` (`accountid`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `runalyze_activity_tag` (
  `activityid` int(10) unsigned NOT NULL,
  `tagid` int(10) unsigned NOT NULL,
  PRIMARY KEY `tagActivity` (`activityid`,`tagid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Running drills' FROM `runalyze_account` WHERE `language` = 'en';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Lauf-ABC' FROM `runalyze_account` WHERE `language` = 'de';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'tècnica' FROM `runalyze_account` WHERE `language` = 'ca';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Loop ABC' FROM `runalyze_account` WHERE `language` = 'nl';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Technice' FROM `runalyze_account` WHERE `language` = 'it';
INSERT INTO `runalyze_tag` (`accountid`, `tag`) SELECT `id`, 'Ćwiczenia biegowe' FROM `runalyze_account` WHERE `language` = 'pl';

INSERT INTO `runalyze_activity_tag` (`activityid`, `tagid`) SELECT tr.id, tg.id FROM `runalyze_training` tr LEFT JOIN `runalyze_tag` tg ON tr.accountid=tg.accountid where `abc` = 1;

UPDATE `runalyze_plugin` SET `key`="RunalyzePluginStat_Tag" WHERE `key`="RunalyzePluginStat_Laufabc";

/* ALTER TABLE `runalyze_tag` ADD PRIMARY KEY (`id`), ADD KEY `accountid` (`accountid`);
ALTER TABLE `runalyze_activity_tag` ADD PRIMARY KEY (`activityid`,`tagid`), ADD KEY `tagid` (`tagid`);*/