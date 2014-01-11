<?php
/**
 * File for displaying a training.
 * Call:   call.SharedTraining.php?url=
 */
require '../inc/class.Frontend.php';
require '../inc/class.FrontendShared.php';

$Frontend = new FrontendShared();

if (FrontendShared::$IS_IFRAME)
	echo '<div id="statistics-inner" class="panel" style="width:97%;margin:0;padding:1%;">';
elseif (!Request::isAjax())
	echo '<div id="statistics-inner" class="panel" style="width:960px;margin:5px auto;">';
else
	echo '<div>';

$Frontend->displaySharedView();

echo '</div>';
?>