UPDATE `runalyze_dataset` set name = 'vdoticon' WHERE name = 'vdot';
INSERT INTO `runalyze_dataset` (`name`, `active`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`)
  SELECT 'vdot', 1, 2, '', '', 0, 1, 'AVG', `id` FROM `runalyze_account`;
