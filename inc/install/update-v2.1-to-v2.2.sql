/* 08.07.2015 - add hrv table */
CREATE TABLE IF NOT EXISTS `runalyze_hrv` ( `accountid` int(10) unsigned NOT NULL, `activityid` int(10) unsigned NOT NULL, `data` longtext ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `runalyze_hrv` ADD PRIMARY KEY (`activityid`), ADD KEY `accountid` (`accountid`);
