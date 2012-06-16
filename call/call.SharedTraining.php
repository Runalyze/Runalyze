<?php
/**
 * File for displaying a training.
 * Call:   call.SharedTraining.php?url=
 */
require '../inc/class.Frontend.php';
require '../inc/class.FrontendShared.php';

$Frontend = new FrontendShared();

if (FrontendShared::$IS_IFRAME)
	echo '<div id="tab_content" class="panel" style="width:97%;margin:0;padding:1%;">';
else
	echo '<div id="tab_content" class="panel" style="width:960px;margin:5px auto;">';

$Frontend->displaySharedView();

echo '</div>';
?>