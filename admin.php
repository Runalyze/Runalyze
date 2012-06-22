<?php
/**
 * RUNALYZE
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @copyright http://www.runalyze.de/
 */
if (!file_exists('config.php')) {
	include 'install.php';
	exit();
}

require 'inc/class.Frontend.php';
$Frontend = new Frontend(true);

$title = 'Runalyze v'.RUNALYZE_VERSION;
$tpl   = 'tpl.adminWindow.php';

$AdminIsLoggedIn = isset($_POST['password']) && $Frontend->isAdminPassword($_POST['password']);
$AllUser         = Mysql::getInstance()->untouchedFetchArray('
	SELECT '.PREFIX.'account.*,
		(
			SELECT SUM('.PREFIX.'training.distance)
			FROM '.PREFIX.'training
			WHERE '.PREFIX.'training.accountid = '.PREFIX.'account.id
		)	AS km,
		(
			SELECT COUNT(*)
			FROM '.PREFIX.'training
			WHERE '.PREFIX.'training.accountid = '.PREFIX.'account.id
		)	AS num
	FROM '.PREFIX.'account
	ORDER BY id ASC');

include 'inc/tpl/tpl.installerHeader.php';
include 'inc/tpl/'.$tpl;
include 'inc/tpl/tpl.installerFooter.php';