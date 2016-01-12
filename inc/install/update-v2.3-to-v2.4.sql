UPDATE runalyze_training SET activity_id = UNIX_TIMESTAMP(activity_id);
ALTER table runalyze_training CHANGE COLUMN `activity_id` `activity_id` int(11) DEFAULT NULL;
UPDATE runalyze_training SET activity_id = time WHERE activity_id IS NULL;