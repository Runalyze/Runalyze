/* Rev280 */
ALTER TABLE runalyze_clothes ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_conf ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_dataset ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_plugin ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_shoe ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_sport ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_training ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_type ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_user ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;

delimiter | CREATE TRIGGER del_tr_train AFTER DELETE ON runalyze_account	FOR EACH ROW BEGIN		DELETE FROM runalyze_clothes WHERE accountid = OLD.id;		DELETE FROM runalyze_conf WHERE accountid = OLD.id;		DELETE FROM runalyze_dataset WHERE accountid = OLD.id;		DELETE FROM runalyze_plugin WHERE accountid = OLD.id;		DELETE FROM runalyze_shoe WHERE accountid = OLD.id;		DELETE FROM runalyze_sport WHERE accountid = OLD.id;		DELETE FROM runalyze_training WHERE accountid = OLD.id;		DELETE FROM runalyze_type WHERE accountid = OLD.id;		DELETE FROM runalyze_user WHERE accountid = OLD.id;	END; | delimiter; 

ALTER TABLE  `runalyze_account` ADD  `deletion_hash` VARCHAR( 32 ) NOT NULL;

/* Rev306 */
ALTER TABLE  `runalyze_conf` DROP  `category`;
ALTER TABLE  `runalyze_training` ADD  `jd_intensity` SMALLINT( 4 ) NOT NULL DEFAULT  '0' AFTER  `vdot`;
ALTER TABLE  `runalyze_dataset` ADD  `active` BOOL NOT NULL DEFAULT  '1' AFTER  `name`;
ALTER TABLE  `runalyze_training` ADD  `no_vdot` BOOL NOT NULL DEFAULT  '0' AFTER  `vdot`;
ALTER TABLE  `runalyze_training` ADD  `created` INT NOT NULL AFTER  `time`, ADD  `edited` INT NOT NULL AFTER  `created`;
ALTER TABLE  `runalyze_training` ADD  `creator` VARCHAR( 100 ) NOT NULL , ADD  `creator_details` TINYTEXT NOT NULL , ADD  `elevation_corrected` BOOL NOT NULL DEFAULT  '0';

/* Rev313 */
ALTER TABLE  `runalyze_training` ADD  `activity_id` VARCHAR( 50 ) NOT NULL DEFAULT  '' AFTER  `creator_details`;

/* Rev324 */
ALTER TABLE  `runalyze_conf` DROP  `type` , DROP  `description` , DROP  `select_description` ;

/* REV336 */
ALTER TABLE  `runalyze_training` DROP  `no_vdot`;
ALTER TABLE  `runalyze_training` ADD  `use_vdot` TINYINT( 1 ) NOT NULL DEFAULT  '1' AFTER  `vdot`;
UPDATE `runalyze_training` SET `use_vdot` = 1;

/* Rev347 */
ALTER TABLE  `runalyze_training` ADD  `gps_cache_object` MEDIUMTEXT NOT NULL;