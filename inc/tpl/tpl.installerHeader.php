<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="stylesheet" type="text/css" href="lib/jquery.jbar.css" />
	<link rel="stylesheet" type="text/css" href="lib/jquery.tablesorter.css" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title><?php echo $title; ?></title>

	<script type="text/javascript" src="lib/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="lib/jquery.backgroundStretch.js"></script>
	<script type="text/javascript" src="lib/jquery.jbar.js"></script>
	<script type="text/javascript" src="lib/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="lib/jquery.tablesorter.pager.js"></script>
	<script type="text/javascript" src="lib/runalyze.lib.js"></script>
	<script type="text/javascript" src="lib/runalyze.lib.log.js"></script>
	<script type="text/javascript" src="lib/runalyze.lib.tablesorter.js"></script>
</head>

<body id="installer">

<?php echo Ajax::wrapJSforDocumentReady('Runalyze.init();'); ?>

<div id="overlay" style="display:block;"></div>
<div id="ajax" class="panel" style="display:block;">
	<h1><?php echo $title; ?></h1>

	<div style="padding:0 70px;">