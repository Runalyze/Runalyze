/* 26.01.2016 - adjust v02max estimate from fit files */
ALTER TABLE `runalyze_training` CHANGE `fit_vdot_estimate` `fit_vdot_estimate` DECIMAL(4,2) UNSIGNED NOT NULL DEFAULT '0.0';