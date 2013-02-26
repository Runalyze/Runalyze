ALTER TABLE  `runalyze_type` ADD  `sportid` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `splits`;
UPDATE `runalyze_type` AS TY LEFT JOIN runalyze_conf AS CO ON TY.accountid = CO.accountid SET TY.sportid = CO.value WHERE CO.key LIKE  'RUNNINGSPORT';
