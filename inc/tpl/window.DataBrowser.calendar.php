<?php
/**
 * File displaying the calendar for the DataBrowser
 * Call:   inc/tpl/window.DataBrowser.calendar.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Frontend->displayHeader();
?>
<h1>Kalenderauswahl</h1>

<div id="widget">
	<div id="widgetField">
		<span>W&auml;hle ein Datum aus ...</span>
		<a href="#">Ausw&auml;hlen</a>
	</div>
	<div id="widgetCalendar">
	</div>
</div>

<div style="height:180px;" />

<?php
#$Frontend->displayFooter();
$Frontend->close();
?>