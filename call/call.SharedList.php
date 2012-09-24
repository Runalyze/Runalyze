<?php
/**
 * File for displaying a training.
 * Call:   call.SharedList.php?user=
 */
require '../inc/class.Frontend.php';
require '../inc/class.FrontendShared.php';
require '../inc/class.FrontendSharedList.php';

$Frontend = new FrontendSharedList();

if (!Request::isAjax()) {
	echo '<div id="dataPanel" class="panel" style="width:960px;margin:5px auto;">';
	echo '<div id="'.DATA_BROWSER_SHARED_ID.'">';
}

$Frontend->displaySharedView();

if (!Request::isAjax()) {
	echo '</div>';
	echo '</div>';

	echo '<div id="tab_content" class="panel" style="width:960px;margin:5px auto;">
		<p class="info">
			Klicke ein Training an, um weitere Details anzuzeigen.<br />
			Trainings, die f&uuml;r die Detailansicht freigegeben sind, sind durch ein '.Icon::$ADD_SMALL_GREEN.' markiert.
		</p>
</div>';
}
?>