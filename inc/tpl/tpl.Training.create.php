<?php
/**
 * File displaying the formular for adding a new training, called via Training::displayCreateWindow()
 */
$Mysql = Mysql::getInstance();

if (isset($_GET['json'])) {
	Error::getInstance()->footer_sent = true;
	move_uploaded_file($_FILES['userfile']['tmp_name'], 'tmp.tcx');
	echo 'success';
	return;
} elseif (isset($_GET['tmp'])) {
	$_POST = Training::parseTcx(file_get_contents('tmp.tcx'));
	unlink('tmp.tcx');
} elseif (isset($_POST['data']))
	$_POST = Training::parseTcx($_POST['data']);

if (isset($_POST['error']))
	Error::getInstance()->addError('Training::parseTcx() meldet: '.$_POST['error']);

$showUploader = empty($_POST);

$Weather = Weather::Forecaster();
$Weather->setPostDataIfEmpty();

// TODO: Move parts to /tpl/
// TODO: ImporterExporter::setDefaultPostDataForCreation()
if (!isset($_POST['s']))
	$_POST['s'] = '0:00:00';
if (!isset($_POST['kcal']))
	$_POST['kcal'] = '0';
if (!isset($_POST['distance']))
	$_POST['distance'] = '0.00';
if (!isset($_POST['pace']))
	$_POST['pace'] = '0:00';
if (!isset($_POST['kmh']))
	$_POST['kmh'] = '0,00';
if (!isset($_POST['elevation']))
	$_POST['elevation'] = '0';
if (!isset($_POST['splits']))
	$_POST['splits'] = '';

// TODO: Onchange-queries with jQuery
?>

<span class="right" id="ajaxLinks">
	<?php echo Ajax::change('TCX-Upload', 'ajax', 'uploadTcx'); ?> |
	<?php echo Ajax::change('Garmin-Upload', 'ajax', 'upload'); ?> |
	<?php echo Ajax::change('Formular', 'ajax', 'formular'); ?>
</span>

<div class="change" id="uploadTcx"<?php if (CONF_TRAINING_CREATE_MODE != 'tcx' || !$showUploader) echo ' style="display:none;"'; ?> onmouseover="javascript:createUploader()">
	<h1>Eine tcx-Datei hochladen</h1>

	<div class="c button" id="file-upload-tcx">Datei hochladen</div>
<script>
function createUploader() {
	$("#file-upload-tcx").removeClass("hide");
	new AjaxUpload('#file-upload-tcx', {
		action: '<?php echo $_SERVER['SCRIPT_NAME']; ?>?json=true',
		onComplete : function(file, response){
			jLoadLink('ajax', '<?php echo $_SERVER['SCRIPT_NAME']; ?>?tmp=true');
		}		
	});
}
</script>
</div>

<div class="change" id="upload"<?php if (CONF_TRAINING_CREATE_MODE != 'garmin' || !$showUploader) echo ' style="display:none;"'; ?>>
	<h1>Training vom Garmin Forerunner hochladen</h1>

	<div style="width:100%;text-align:center;position:relative;">
		<small style="position:absolute;right:0;">Bei Problemen: <?php echo '<img class="link" style="vertical-align:middle;" src="'.Icon::getSrc(ICON::$REFRESH).'" onclick="$(\'#GCapi\').attr(\'src\', \'inc/tpl/tpl.garminCommunicator.php\')" />'; ?></small>
		<iframe src="inc/tpl/tpl.garminCommunicator.php" id="GCapi" width="550px" height="180px"></iframe>
	</div>
</div>

<div class="change" id="formular"<?php if (CONF_TRAINING_CREATE_MODE != 'form' && $showUploader) echo ' style="display:none;"'; ?>>
<form id="newtraining" class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">

	<input type="hidden" name="type" value="newtraining" />
	<input type="hidden" id="kalorien_stunde" name="kalorienprostunde" value="0" />

	<h1>Neues Training</h1>

	<div class="c">
<?php
$sports = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'sport` ORDER BY `id` ASC');
foreach($sports as $sport) {
	$onclick = 'kps('.$sport['kcal'].');';
	$onclick .= ($sport['distances'] == 1) ? 'show(\'distanz\');' : 'unshow(\'distanz\');';
	$onclick .= ($sport['types'] == 1) ? 'show(\'typen\');' : 'unshow(\'typen\');unshow(\'splits\');';
	$onclick .= ($sport['pulse'] == 1) ? 'show(\'puls\');' : 'unshow(\'puls\');';
	$onclick .= ($sport['outside'] == 1) ? 'show(\'outside\');' : 'unshow(\'outside\');';

	echo('
		<label><input type="radio" name="sportid" value="'.$sport['id'].'" onClick="show(\'normal\');'.$onclick.'" /> '.$sport['name'].'</label> &nbsp; '.NL);
}

if (isset($_POST['sportid']))
	echo('<script type="text/javascript">$("input[value=\''.$_POST['sportid'].'\']").click();</script>');
?>
	</div>
		<br />

	<div style="float: left;">
		<span id="normal" style="display: none;">
			<label>
				<?php echo HTML::simpleInputField('datum', 10, date('d.m.Y')); ?>
				<?php echo HTML::simpleInputField('zeit', 4, '00:00'); ?>
				<small>Datum</small>
			</label><br />
			<label>
				<input type="text" size="8" name="s" id="dauer" value="<?php echo $_POST['s']; ?>" onChange="paceberechnung(); kalorienberechnung(); kmhberechnung();" />
				<small style="margin-right: 75px;">Dauer</small>
			</label>
			<label>
				<input type="text" size="4" name="kcal" id="kalorien" value="<?php echo $_POST['kcal']; ?>" />
				<small>kcal</small>
			</label><br />
			<label>
				<?php echo HTML::simpleInputField('comment', 50); ?>
				<small>Bemerkung</small>
			</label><br />
			<label>
				<?php echo HTML::simpleInputField('partner', 50); ?>
				<small>Trainingspartner</small>
			</label>
		</span>
	</div>

	<div style="float: right; width: 45%;"><br />
		<span id="typen" style="display: none;">
			<select name="typeid">
<?php
// TODO
// <input type="hidden" name="typeids_with_splits" value: Type::getIdsWithSplitsAsString();
// -> jQuery-check
$typen = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'type` ORDER BY `id` ASC');
if (empty($typen))
	echo('
				<option value="0">keine Typen vorhanden</option>');
else
	echo('
				<option value="0">?</option>');
foreach($typen as $typ) {
	$onClick = '';
	if ($typ['splits'] == 1)
		$onClick .= '$(\'#splits\').removeClass(\'hide\')';//'document.getElementById(\'splits\').style.display=\'block\'';
	else
		$onClick .= '$(\'#splits\').addClass(\'hide\')';//'document.getElementById(\'splits\').style.display=\'none\'';

	$selected = isset($_POST['typeid']) ? HTML::Selected($_POST['typeid'], $typ['id']) : '';
	echo('
				<option value="'.$typ['id'].'" onClick="'.$onClick.'"'.$selected.'>'.$typ['name'].'</option>');
}
?>
			</select>

			<?php echo Shoe::getSelectBox(); ?>

			<label>
				<?php echo HTML::checkBox('abc'); ?>
				<small>Lauf-ABC</small>
			</label>
		</span>

		<span id="distanz" style="display: none;">
			<label>
				<input type="text" size="4" name="distance" id="dist" value="<?php echo Helper::Unknown($_POST['distance'], '0.00'); ?>" onChange="paceberechnung(); kmhberechnung();" />
				<small>km</small>
			</label>
			<label>
				<?php echo HTML::checkBox('is_track'); ?>
				<small style="margin-right: 25px;">Bahn</small>
			</label>
			<label>
				<input type="text" size="4" name="pace" id="pace" value="<?php echo Helper::Unknown($_POST['pace'], '0:00'); ?>" disabled="disabled" />
				<small>/km</small>
			</label>
			<label>
				<input type="text" size="4" name="kmh" id="kmh" value="<?php echo Helper::Unknown($_POST['kmh'], '0,00'); ?>" disabled="disabled" />
				<small>km/h</small>
			</label>
			<label>
				<input type="text" size="3" name="elevation" value="<?php echo Helper::Unknown($_POST['elevation'], '0'); if (isset($_POST['arr_alt'])) echo '" disabled="disabled'; ?>" />
				<small>HM</small>
			</label>
		</span>

		<span id="puls" style="display: none;">
			<label>
				<?php echo HTML::simpleInputField('pulse_avg', 3, '0'); ?>
				<small style="margin-right: 73px;">Puls</small>
			</label>
			<label>
				<?php echo HTML::simpleInputField('pulse_max', 3, '0'); ?>
				<small>max. Puls</small>
			</label>
		</span>
	</div>

		<br class="clear" />

	<span id="outside" style="display: none;">

		<?php echo HTML::hiddenInput('arr_time'); ?>
		<?php echo HTML::hiddenInput('arr_lat'); ?>
		<?php echo HTML::hiddenInput('arr_lon'); ?>
		<?php echo HTML::hiddenInput('arr_alt'); ?>
		<?php echo HTML::hiddenInput('arr_dist'); ?>
		<?php echo HTML::hiddenInput('arr_heart'); ?>
		<?php echo HTML::hiddenInput('arr_pace'); ?>

		<br />
		<label>
			<?php echo HTML::simpleInputField('route', 50); ?>
			<small style="margin-right: 100px;">Strecke</small>
		</label>
		<label>
			<?php echo Weather::getSelectBox(); ?>
			<small>Wetter</small>
		</label>
		<label>
			<?php echo HTML::simpleInputField('temperature', 2); ?>
			<small style="margin-right: 40px;">&#176;C</small>
		</label>
			<br />
			<br />
		<?php echo Clothes::getCheckboxes(); ?>
			<br />
	</span>

	<span id="splits" class="hide">
		<br />
		<label>
			<?php echo HTML::textarea('splits', 70, 3); ?>
			<small>Splits</small>
		</label><br />
	</span>

	<div class="c">
		<input style="margin-top: 10px;" type="submit" value="Eintragen!" />
	</div>
</form>
</div>