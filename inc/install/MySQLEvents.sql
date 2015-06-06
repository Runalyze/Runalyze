/* 04.06.2015 MySQL Event to delete registered user which have not been activated (7 days) */
DELIMITER |
CREATE EVENT deleteNotActivatedUsers
	ON SCHEDULE EVERY 1 DAY
	DO
		BEGIN 
			DELETE FROM runalyze_account WHERE registerdate < UNIX_TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL -7 DAY)) AND activation_hash != '';
END |
DELIMITER ;
