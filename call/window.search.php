<?php
/**
 * File the search
 * Call:   call/window.search.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

$showResults = !empty($_POST);

if (isset($_GET['get']) && $_GET['get'] == 'true') {
	$_POST = array_merge($_POST, $_GET);
	$showResults = true;
}

if (empty($_POST) || Request::param('get') == 'true') {
	$Formular = new SearchFormular();
	$Formular->display();
}

$Results = new SearchResults($showResults);
$Results->display();