-- Statements to use InnoDB as engine
--
-- InnoDB support e.g. row-level locking instead of table locking
-- and should be used for large installations.
-- To work properly, InnoDB requires some advanced database settings.

-- You may need to set the following in your my.cnf if you do not have rights to use SET GLOBAL
-- innodb_file_per_table   = 1
-- innodb_file_format      = Barracuda
SET GLOBAL innodb_file_format=barracuda;
SET GLOBAL innodb_file_per_table=ON;

-- Tables in v1.5
ALTER TABLE `runalyze_user` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_account` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_sport` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_conf` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_plugin` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_type` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_dataset` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_training` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;

-- Tables since v2.0
ALTER TABLE `runalyze_plugin_conf` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_route` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
ALTER TABLE `runalyze_trackdata` ENGINE=InnoDB ROW_FORMAT=DYNAMIC;
