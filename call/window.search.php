<?php
/**
 * File the search
 * Call:   call/window.search.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

if (isset($_GET['get']) && $_GET['get'] == 'true')
	$_POST = array_merge($_POST, $_GET);

if (empty($_POST)) {
	$Formular = new SearchFormular();
	$Formular->display();
}

$Results = new SearchResults();
$Results->display();
exit;

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


if (!($_GET['pager'] == 'true') && !isset($_POST['send_to_multiEditor'])) {
	echo '<div id="'.DATA_BROWSER_SEARCH_ID.'">';
	include '../inc/tpl/tpl.Search.formular.php';
}

echo '<div id="'.DATA_BROWSER_SEARCHRESULT_ID.'">';

if ($submit)
	include '../inc/tpl/tpl.Search.result.php';

echo '</div>';

if (!($_GET['pager'] == 'true'))
	echo '</div>';
?>