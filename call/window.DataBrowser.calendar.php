<?php
/**
 * File displaying the calendar for the DataBrowser
 * Call:   call/window.DataBrowser.calendar.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();
?>
<h1>Kalenderauswahl</h1>

<div id="widget">
	<small class="right">
		zur Best&auml;tigung wieder das Icon anklicken
	</small>

	<div id="widgetField">
		<span>W&auml;hle ein Datum aus ...</span>
		<a href="#">Ausw&auml;hlen</a>
	</div>

	<div id="widgetCalendar">
	</div>
</div>

<div style="height:180px;"></div>

<?php
#$Frontend->displayFooter();
$Frontend->close();
?>