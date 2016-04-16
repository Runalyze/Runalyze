/* 15.04.2016 - add default sport type id to sport table*/
ALTER TABLE `runalyze_sport` ADD `default_typeid` int(10) unsigned DEFAULT NULL AFTER `main_equipmenttypeid`;
