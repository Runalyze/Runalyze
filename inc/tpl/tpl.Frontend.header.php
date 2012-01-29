<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="stylesheet"  type="text/css"href="lib/datepicker.css" />
	<link rel="stylesheet"  type="text/css"href="lib/tablesorter.css" />
	<link rel="stylesheet"  type="text/css"href="lib/jquery.tooltip.css" />
	<link rel="stylesheet"  type="text/css"href="lib/flot.css" />
	<link rel="stylesheet"  type="text/css"href="lib/flot/qtip.css" />
	<?php foreach ($this->CSS_FILES as $file): ?>
		<link rel="stylesheet"  type="text/css"href="<?php echo $file; ?>" />
	<?php endforeach; ?>

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
	<script type="text/javascript" src="lib/jquery.tooltip.js"></script>

	<script type="text/javascript" src="lib/runalyze.lib.js"></script>

	<?php if (class_exists('Plot')): ?>
	<?php foreach (Plot::getNeededJSFilesAsArray() as $file): ?>
		<script type="text/javascript" src="<?php echo $file; ?>"></script>
	<?php endforeach; ?>
	<?php endif; ?>

	<?php foreach ($this->JS_FILES as $file): ?>
		<script type="text/javascript" src="<?php echo $file; ?>"></script>
	<?php endforeach; ?>

	<!--[if IE]><style type="text/css">table { border-collapse: collapse; }</style><![endif]-->
	<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="lib/flot/excanvas.min.js"></script><![endif]-->
</head>

<body id="home" style="background-image:url(<?php echo CONF_DESIGN_BG_FILE; ?>);">

<div id="flotLoader"></div>
<div id="overlay"></div>
<div id="ajax" class="panel"></div>