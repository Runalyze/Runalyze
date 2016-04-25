/* 15.04.2016 - add default sport type id to sport table*/
ALTER TABLE `runalyze_sport` ADD `default_typeid` int(10) unsigned DEFAULT NULL AFTER `main_equipmenttypeid`;

/* 24.04.2016 - add training effect & add RPE to activity (training) table*/
ALTER TABLE `runalyze_training` ADD `fit_training_effect` decimal(2,1) unsigned DEFAULT NULL AFTER `fit_hrv_analysis`, ADD `rpe` tinyint(2) unsigned DEFAULT NULL AFTER `jd_intensity`; 
