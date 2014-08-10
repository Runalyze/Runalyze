/* Rev 736 */
INSERT INTO `runalyze_dataset` (`name`, `label`, `description`, `distance`, `outside`, `pulse`, `type`, `modus`, `class`, `style`, `position`, `summary`, `summary_mode`, `accountid`) SELECT 'jd_intensity', 'JD-Intensit&auml;t', 'Anzeige der Trainingspunkte nacht Jack Daniels', 1, 0, 1, 1, 1, '', '', 22, 1, 'SUM', `id` FROM `runalyze_account`;

/* Rev 794 */
ALTER TABLE `runalyze_plugin` DROP `name`, DROP `description`;