/* Rev615 */
ALTER TABLE `runalyze_training` ADD INDEX `time` (`time`);
ALTER TABLE `runalyze_training` ADD INDEX `sportid` (`sportid`);
ALTER TABLE `runalyze_training` ADD INDEX `typeid` (`typeid`);

ALTER TABLE `runalyze_user` ADD INDEX `time` (`time`);