-- Statements to set `accountid` to '1' everywhere (you may need to use your own prefix)
-- MAKE SURE THAT YOUR NEWLY CREATED ACCOUNT HAS ID "1"!
--
-- RUNALYZE v2.2+ requires even single-user installations to have a real account

-- Remove default entries of new account
DELETE FROM `runalyze_clothes` WHERE `accountid`='1';
DELETE FROM `runalyze_conf` WHERE `accountid`='1';
DELETE FROM `runalyze_dataset` WHERE `accountid`='1';
DELETE FROM `runalyze_plugin` WHERE `accountid`='1';
DELETE FROM `runalyze_route` WHERE `accountid`='1';
DELETE FROM `runalyze_shoe` WHERE `accountid`='1';
DELETE FROM `runalyze_sport` WHERE `accountid`='1';
DELETE FROM `runalyze_trackdata` WHERE `accountid`='1';
DELETE FROM `runalyze_training` WHERE `accountid`='1';
DELETE FROM `runalyze_type` WHERE `accountid`='1';
DELETE FROM `runalyze_user` WHERE `accountid`='1';

-- Move all existing data to new account
UPDATE `runalyze_clothes` SET `accountid`='1';
UPDATE `runalyze_conf` SET `accountid`='1';
UPDATE `runalyze_dataset` SET `accountid`='1';
UPDATE `runalyze_plugin` SET `accountid`='1';
UPDATE `runalyze_route` SET `accountid`='1';
UPDATE `runalyze_shoe` SET `accountid`='1';
UPDATE `runalyze_sport` SET `accountid`='1';
UPDATE `runalyze_trackdata` SET `accountid`='1';
UPDATE `runalyze_training` SET `accountid`='1';
UPDATE `runalyze_type` SET `accountid`='1';
UPDATE `runalyze_user` SET `accountid`='1';
