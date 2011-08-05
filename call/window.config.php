<?php
/**
 * File displaying the config panel
 * Call:   call/window.config.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

if (isset($_POST) && isset($_POST['formID']) && $_POST['formID'] == "config") {
	Config::parsePostDataForConf();
	Config::parsePostDataForPlugins();
	Config::parsePostDataForDataset();
	Config::parsePostDataForSports();
	Config::parsePostDataForTypes();
	Config::parsePostDataForClothes();

	$submit = '<em>Die Einstellungen wurden gespeichert!</em><br /><br />';
}

$Frontend->displayHeader();

if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="config" onsubmit="return false;" method="post">
	<div id="config_all">
		<input type="hidden" name="formID" value="config" />

		<span class="right">
			<a class="change" href="#config_allgemein" target="config_all">Allgemeines</a> |
			<a class="change" href="#config_plugins" target="config_all">Plugins</a> |
			<a class="change" href="#config_dataset" target="config_all">Dataset</a> |
			<a class="change" href="#config_sport" target="config_all">Sportarten</a> |
			<a class="change" href="#config_typen" target="config_all">Trainingstypen</a> |
			<a class="change" href="#config_kleidung" target="config_all">Kleidung</a>
		</span>

		<div id="config_allgemein" class="change">
			<?php include '../inc/tpl/tpl.Config.allgemein.php' ?>
		</div>
		
		<div id="config_plugins" class="change" style="display:none;">
			<?php /* TODO: Download-Link */ ?>
			<?php include '../inc/tpl/tpl.Config.plugins.php' ?>
		</div>
		
		<div id="config_dataset" class="change" style="display:none;">
			<?php include '../inc/tpl/tpl.Config.dataset.php' ?>
		</div>
		
		<div id="config_sport" class="change" style="display:none;">
			<?php include '../inc/tpl/tpl.Config.sports.php' ?>
		</div>
		
		<div id="config_typen" class="change" style="display:none;">
			<?php include '../inc/tpl/tpl.Config.typen.php' ?>
		</div>
		
		<div id="config_kleidung" class="change" style="display:none;">
			<?php include '../inc/tpl/tpl.Config.kleidung.php' ?>
		</div>

		<div class="c">
			<input type="submit" value="Einstellungen speichern" />
		</div>
	</div>
</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>