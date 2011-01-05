<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>LaufTageBuch v<?php echo LTB_VERSION; ?></title>

	<script type="text/javascript" src="lib/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="lib/jquery.scrollTo-min.js"></script>
	<script type="text/javascript" src="lib/form_scripts.js"></script>
	<script type="text/javascript" src="lib/ajax.js"></script>
</head>

<?php // Find date for first page in DataBrowser
// TODO May be done by class::DataBrowser later
$today = time();
$last_training = $mysql->fetch('ltb_training','LAST');
if ($last_training !== false)
	$today = $last_training['time'];
$start = Helper::Wochenstart($today);
$ende = Helper::Wochenende($today);
?>
<?php $error->add('TODO','class::DataBrowser has to chose first page on its own',__FILE__,__LINE__); ?>
<body onload="daten(<?php echo("'$today','$start','$ende'"); ?>)">

<div id="overlay"></div>
<div id="ajax" class="panel"></div>