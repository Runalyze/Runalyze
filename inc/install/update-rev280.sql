ALTER TABLE runalyze_clothes ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_conf ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_dataset ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_plugin ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_shoe ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_sport ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_training ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_type ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;
ALTER TABLE runalyze_user ADD FOREIGN KEY (`accountid`) REFERENCES runalyze_account(id) ON DELETE CASCADE;

delimiter |
CREATE TRIGGER del_tr_train AFTER DELETE ON runalyze_account
	FOR EACH ROW BEGIN
		DELETE FROM runalyze_clothes WHERE accountid = OLD.id;
		DELETE FROM runalyze_conf WHERE accountid = OLD.id;
		DELETE FROM runalyze_dataset WHERE accountid = OLD.id;
		DELETE FROM runalyze_plugin WHERE accountid = OLD.id;
		DELETE FROM runalyze_shoe WHERE accountid = OLD.id;
		DELETE FROM runalyze_sport WHERE accountid = OLD.id;
		DELETE FROM runalyze_training WHERE accountid = OLD.id;
		DELETE FROM runalyze_type WHERE accountid = OLD.id;
		DELETE FROM runalyze_user WHERE accountid = OLD.id;
	END;
|
delimiter ;

ALTER TABLE  `runalyze_account` ADD  `deletion_hash` VARCHAR( 32 ) NOT NULL;