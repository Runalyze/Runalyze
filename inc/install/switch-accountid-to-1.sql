-- Statements to set `accountid` to '1' everywhere (you may need to use your own prefix)
--
-- RUNALYZE v2.2+ requires even single-user installations to have a real account

UPDATE `runalyze_conf` SET `accountid`='1';
UPDATE `runalyze_dataset` SET `accountid`='1';
UPDATE `runalyze_plugin` SET `accountid`='1';
UPDATE `runalyze_route` SET `accountid`='1';
UPDATE `runalyze_sport` SET `accountid`='1';
UPDATE `runalyze_trackdata` SET `accountid`='1';
UPDATE `runalyze_training` SET `accountid`='1';
UPDATE `runalyze_type` SET `accountid`='1';
UPDATE `runalyze_user` SET `accountid`='1';
