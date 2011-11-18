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
	echo Ajax::wrapJS('$("input[value=\''.$_POST['sportid'].'\']").click();');
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
		$onClick .= '$(\'#splits\').removeClass(\'hide\')';
	else
		$onClick .= '$(\'#splits\').addClass(\'hide\')';

	$selected = isset($_POST['typeid']) ? HTML::Selected($_POST['typeid'], $typ['id']) : '';
	echo('
				<option value="'.$typ['id'].'" onClick="'.$onClick.'"'.$selected.'>'.$typ['name'].'</option>');
}
?>
			</select>

			<?php echo Shoe::getSelectBox(); ?>
			
			<label>
				<?php echo HTML::checkBox('abc', -1, true); ?>
				<small>Lauf-ABC</small>
			</label>
		</span>

		<span id="distanz" style="display: none;">
			<label>
				<input type="text" size="4" name="distance" id="dist" value="<?php echo Helper::Unknown($_POST['distance'], '0.00'); ?>" onChange="paceberechnung(); kmhberechnung();" />
				<small>km</small>
			</label>
			<label>
				<?php echo HTML::checkBox('is_track', -1, true); ?>
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