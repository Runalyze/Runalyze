<?php
/**
 * File the search
 * Call:   inc/tpl/window.search.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);

$Frontend->displayHeader();

if ($_GET['get'] == 'true')
	$_POST = array_merge($_POST, $_GET);

if (sizeof($_POST) > 0) {
	$submit = true;
	if (!isset($_POST['order'])) {
		$_POST['order'] = 'time';
		$_POST['sort'] = 'DESC';
	}
}


if (!($_GET['pager'] == 'true')) {
	echo('<h1>Suche</h1>');
	echo('<div id="'.DATA_BROWSER_SEARCH_ID.'">');

	include('tpl.Search.formular.php');
}
if ($submit) {
	echo('<div id="'.DATA_BROWSER_SEARCHRESULT_ID.'">');
	include('tpl.Search.result.php');
	$Frontend->displayFooter();
	echo('</div>');
} else
	$Frontend->displayFooter();

if (!($_GET['pager'] == 'true'))
	echo('</div>');

$Frontend->close();
?>