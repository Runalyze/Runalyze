INSERT INTO `runalyze_type` (`name`, `abbr`, `RPE`, `splits`, `sportid`, `accountid`)
SELECT DISTINCT TY.name, TY.abbr, TY.RPE, TY.splits, TR.sportid, TR.accountid FROM `runalyze_training` AS TR 
INNER JOIN `runalyze_type` AS TY ON TR.typeid = TY.id 
WHERE TR.sportid IN (SELECT id FROM `runalyze_sport` WHERE `name` != 'Laufen' AND types = 1) AND TR.typeid != 0;
UPDATE `runalyze_training` AS TR
INNER JOIN `runalyze_type` AS TY ON TR.sportid = TY.sportid AND TR.accountid = TY.accountid AND TY.name = (SELECT name FROM  `runalyze_type` WHERE `id` = TR.typeid)
SET TR.typeid = TY.id 
WHERE TR.sportid IN (SELECT id FROM `runalyze_sport` WHERE `name` != 'Laufen' AND types = 1) AND TR.typeid != 0;