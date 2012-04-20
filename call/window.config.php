<?php
/**
 * File displaying the config panel
 * Call:   call/window.config.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend();

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

if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="config" onsubmit="return false;" method="post">
	<div id="config_all">
		<input type="hidden" name="formID" value="config" />

<?php
$Links   = array();
$Links[] = array('tag' => Ajax::change('Allgemeines', 'config_all', 'config_allgemein'));
$Links[] = array('tag' => Ajax::change('Plugins', 'config_all', 'config_plugins'));
$Links[] = array('tag' => Ajax::change('Dataset', 'config_all', 'config_dataset'));
$Links[] = array('tag' => Ajax::change('Sportarten', 'config_all', 'config_sport'));
$Links[] = array('tag' => Ajax::change('Trainingstypen', 'config_all', 'config_typen'));
$Links[] = array('tag' => Ajax::change('Kleidung', 'config_all', 'config_kleidung'));

echo Ajax::toolbarNavigation($Links, 'right');
?>

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

<?php echo Ajax::wrapJSforDocumentReady('Runalyze.ajax.removeClass("smallWin");'); ?>