<?php
include('config/functions.php');
include('config/dataset.php');
include('config/globals.php');
connect();
config();
if ($_GET['action']=="do")
	include('config/mysql_query.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=ISO-8859-1" http-equiv="content-type" />

	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />

	<title>LaufTageBuch v<?php echo($global['version']); ?></title>

	<script type="text/javascript" src="lib/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="lib/jquery.scrollTo-min.js"></script>
	<script type="text/javascript" src="lib/form_scripts.js"></script>
	<script type="text/javascript" src="lib/ajax.js"></script>
</head>

<?php
$today = time();
$start = wochenstart($today);
$ende = wochenende($today);
?>
<body onload="daten(<?php echo("'$today','$start','$ende'"); ?>)">

<div id="info" class="ajax">
	<div id="info_results" class="panel">
	</div>
	<div id="info_diagramm" class="panel">
	</div>
	<div id="formular" class="panel">
<?php include('lib/formular.php'); ?>
	</div>
</div>

<div id="sucher" class="ajax">
	<div id="suche" class="panel">
	</div>
</div>


<div id="r">

<?php
// Panel
$panel_db = mysql_query('SELECT * FROM `ltb_modules` WHERE `use`=1 ORDER BY `order` ASC');
while ($panel = mysql_fetch_assoc($panel_db)):
$global['panel'][$panel['panel']] = 1;
?>
<div class="panel">
<h1><span class="right"> <?php echo $panel['right']; ?> </span> <span
	class="updowns"> <a
	href="?action=up&order=<?php echo ($panel['order'].'&id='.$panel['id']); ?>">
<img src="img/up.gif" /> </a><br />
<a
	href="?action=down&order=<?php echo ($panel['order'].'&id='.$panel['id']); ?>">
<img src="img/down.gif" /> </a> </span> <span class="link"
	title="<?php echo $panel['beschreibung']; ?>"
	onclick="clap('<?php echo $panel['id']; ?>')"> <?php echo $panel['name']; ?>
</span></h1>
<div id="panel-<?php echo $panel['id']; ?>"
<?php if ($panel['clapped'] == 1) echo (' style="display:none;"'); ?>>
	<?php include('lib/panel_'.$panel['panel'].'.php'); ?></div>
</div>
	<?php endwhile; ?></div>

<div id="l">
<div id="daten">
<div id="daten_results" class="panel"></div>
</div>

<ul class="tabs">
	<li class="active"><a href="lib/stats/index.php" rel="statistiken">Statistiken</a></li>
	<?php if (check_modus('typid') != 0): ?>
	<li><a href="lib/stats/analyse.php" rel="statistiken">Analyse</a></li>
	<?php endif; ?>
	<?php $db = mysql_query('SELECT * FROM `ltb_training` WHERE `typid`='.$global['wettkampf_typid'].' LIMIT 1'); ?>
	<?php if (mysql_num_rows($db) != 0): ?>
	<li><a href="lib/stats/wettkaempfe.php" rel="statistiken">Wettk&auml;mpfe</a></li>
	<?php endif; ?>
	<?php if (check_modus('schuhid') != 0): ?>
	<li><a href="lib/stats/schuhe.php" rel="statistiken">Schuhe</a></li>
	<?php endif; ?>
	<?php if (check_modus('wetterid') != 0): ?>
	<li><a href="lib/stats/wetter.php" rel="statistiken">Wetter</a></li>
	<?php elseif (check_modus('kleidung') != 0): ?>
	<li><a href="lib/stats/kleidung.php" rel="statistiken">Kleidung</a></li>
	<?php endif; ?>
	<li><a href="lib/stats/sonstiges.php" rel="statistiken">Sonstiges</a></li>
</ul>
<div id="statistiken" class="panel tabs">
<div id="tab_content"><?php $include_sports = true; include('lib/stats/index.php'); ?>
</div>
</div>
</div>

<br class="clear" />

<div id="copy"><span class="right"> &copy; Programmierung und Design von
<a id="copy" href="http://www.laufhannes.de/" title="Laufhannes"
	target="_blank">laufhannes.de</a> </span> <span class="left b"> <img
	class="link" onclick="seite('config')" src="img/gear.png"
	style="margin: 0;" title="Einstellungen" /> LaufTageBuch v<?php echo($global['version']); ?>
</span>

<center><img id="wait" src="img/loading.gif" /></center>
</div>
</body>
</html>
<?php
close();
?>