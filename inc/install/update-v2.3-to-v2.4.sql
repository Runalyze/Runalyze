/*29.12.2015 */
ALTER TABLE runalyze_training ADD `wind_speed` tinyint unsigned DEFAULT NULL, ADD `wind_deg` decimal(5,2) unsigned DEFAULT NULL, ADD `humidity` tinyint unsigned DEFAULT NULL, ADD `pressure` smallint unsigned DEFAULT NULL, ADD `is_night` tinyint(1) unsigned;
