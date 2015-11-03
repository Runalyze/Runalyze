/* 18.10.2015 - drop 'types' flag for sport */
ALTER TABLE `runalyze_sport` DROP `types`;

/* 02.11.2015 - geohashes for _route table */
ALTER TABLE `runalyze_route` ADD `geohashes` longtext AFTER `lngs`, ADD `startpoint` char(10) AFTER `startpoint_lng`, ADD `endpoint` char(10) AFTER `endpoint_lng`,  ADD `min` char(10) AFTER `min_lng`, ADD `max` char(10) AFTER `max_lng`;