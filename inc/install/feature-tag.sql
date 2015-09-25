CREATE TABLE IF NOT EXISTS `runalyze_tag` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `accountid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY  `tagAccount` (`accountid`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `runalyze_tag_activity` (
  `activityid` int(11) NOT NULL,
  `tagid` int(11) NOT NULL,
  PRIMARY KEY `tagActivity` (`activityid`,`tagid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
