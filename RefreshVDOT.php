<?php
/**
 * Refresh all VDOT-Values in database
 * Call:   RefreshVDOT.php
 */
require_once 'inc/class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);
$Mysql    = Mysql::getInstance();

// REFRESH VDOT
$IDs = $Mysql->fetchAsArray('SELECT `id` FROM `ltb_training` WHERE `sportid`='.RUNNINGSPORT.' && `puls`!=0');
foreach ($IDs as $ID) {
	$VDOT = JD::Training2VDOT($ID['id']);
	$Mysql->update('ltb_training', $ID['id'], 'vdot', $VDOT);
	echo("#".$ID['id']."   -   $VDOT<br />");
}
?>