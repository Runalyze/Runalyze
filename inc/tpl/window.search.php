<?php
/**
 * File the search
 * Call:   inc/tpl/window.search.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);

$Frontend->displayHeader();

$submit = false;

if (isset($_GET['get']) && $_GET['get'] == 'true')
	$_POST = array_merge($_POST, $_GET);
if (sizeof($_POST) > 0)
	$submit = true;

if (!isset($_POST['order']))
	$_POST['order'] = 'time';
if (!isset($_POST['sort']))
	$_POST['sort'] = 'DESC';
if (!isset($_GET['pager']))
	$_GET['pager'] = 'false';


if (!($_GET['pager'] == 'true')) {
	// TODO: Laden von vorgefertigten Suchen => Zweimal <h1>
	echo '<div id="'.DATA_BROWSER_SEARCH_ID.'">';
	echo '<h1>Suche</h1>';

	include('tpl.Search.formular.php');
}

echo('<div id="'.DATA_BROWSER_SEARCHRESULT_ID.'">');

if ($submit)
	include('tpl.Search.result.php');
	
$Frontend->displayFooter();
echo('</div>');

if (!($_GET['pager'] == 'true'))
	echo('</div>');

$Frontend->close();
?>