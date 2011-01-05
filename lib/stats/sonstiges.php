<?php
header('Content-type: text/html; charset=ISO-8859-1');
include_once('../../config/functions.php');
connect();
?>
<small class="right">
	<a class="ajax" href="lib/stats/sonstiges.php?dat=rek" target="tab_content">Rekorde</a> |
<?php if (check_modus('strecke') != 0): ?>
	<a class="ajax" href="lib/stats/sonstiges.php?dat=strecke" target="tab_content">Strecken</a> |
<?php endif; ?>
	<a class="ajax" href="lib/stats/sonstiges.php?dat=zeiten" target="tab_content">Trainingszeiten</a> |
<?php if (check_modus('trainingspartner') != 0): ?>
	<a class="ajax" href="lib/stats/sonstiges.php?dat=partner" target="tab_content">Trainingspartner</a> |
<?php endif; ?>
<?php if (check_modus('hm') != 0): ?>
	<a class="ajax" href="lib/stats/sonstiges.php?dat=hm" target="tab_content">H&ouml;henmeter</a> |
<?php endif; ?>
<?php if (check_modus('laufabc') != 0): ?>
	<a class="ajax" href="lib/stats/sonstiges.php?dat=abc" target="tab_content">Lauf-ABC</a>
<?php endif; ?>
</small>

<?php
if ($_GET['dat'] == "" || $_GET['dat'] == "undefined")
	$_GET['dat'] = "rek";
include('sonstiges_'.$_GET['dat'].'.php');
?>