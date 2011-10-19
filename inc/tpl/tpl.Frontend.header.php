<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="stylesheet"  type="text/css"href="lib/datepicker.css" />
	<link rel="stylesheet"  type="text/css"href="lib/tablesorter.css" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>Runalyze v<?php echo RUNALYZE_VERSION; ?></title>

	<script type="text/javascript" src="lib/jQuery.1.4.2.js"></script>
	<script type="text/javascript" src="lib/jQuery.scrollTo.js"></script>
	<script type="text/javascript" src="lib/jQuery.form.js"></script>
	<?php if (CONF_DESIGN_BG_FIX_AND_STRETCH): ?>
	<script type="text/javascript" src="lib/jQuery.backgroundStretch.js"></script>
	<?php endif; ?>
	<script type="text/javascript" src="lib/jQuery.metadata.js"></script>
	<script type="text/javascript" src="lib/jQuery.tablesorter.js"></script>
	<script type="text/javascript" src="lib/jQuery.tablesorter.pager.js"></script>
	<script type="text/javascript" src="lib/form_scripts.js"></script>
	<script type="text/javascript" src="lib/ajax.js"></script>
	<script type="text/javascript" src="lib/datepicker.js"></script>
	<script type="text/javascript" src="lib/fileuploader.js"></script>
	<!--[if IE]>
	<style type="text/css">
	table { border-collapse: collapse; }
	</style>
	<![endif]-->
</head>

<body id="main" style="background-image:url(<?php echo CONF_DESIGN_BG_FILE; ?>);">

<div id="overlay"></div>
<div id="ajax" class="panel"></div>