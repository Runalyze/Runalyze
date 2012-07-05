<?php if ($this->inserted): ?>
	<em>Die Trainings wurden hinzugef&uuml;gt.</em>
	<br />
	<br />
	<?php if (!is_null($this->MultiEditor)) $this->MultiEditor->display(); ?>
</div>
<?php exit; ?>
<?php else: ?>
<form id="upload" class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">

	<?php echo Ajax::wrapJSforDocumentReady('$("#ajax").addClass("bigWin");'); ?>

	<?php echo HTML::hiddenInput('csvFileContent', htmlspecialchars($this->FileContent)); ?>
	<?php echo HTML::hiddenInput('forceAsFileName', 'ForcedName.csv'); ?>

	<h1>CSV-Datei importieren</h1>

	<strong>Standardwerte:</strong><br />
	<div class="left w33"><?php echo HTML::simpleInputField('year', 10, date("Y")); ?> Jahr</div>
	<div class="left w33"><?php echo Sport::getSelectBox(CONF_MAINSPORT); ?> Sportart</div>
	<div class="left w33"><?php echo Type::getSelectBox(); ?> Trainingstyp</div>
		<br class="clear" />

<div style="width:100%;max-height:400px;overflow:scroll;">
	<table>
		<thead>
			<tr>
				<td><?php echo Icon::get(Icon::$ADD, '', '', 'Importieren'); ?></td>
				<?php for ($i = 0; $i < $this->RowLength; $i++): ?>
				<td>
					<select name="key[<?php echo $i; ?>]">
						<?php foreach ($this->PossibleKeys as $key => $name): ?>
						<option value="<?php echo $key; ?>"><?php echo $name; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<?php endfor; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->Rows as $i => $row): ?>
			<tr class="small <?php echo HTML::trClass($i); ?>">
				<td><input type="checkbox" name="import[<?php echo $i; ?>]" checked="checked" />
				<?php foreach ($row as $column): ?>
				<td><?php echo $column; ?></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

	<div class="c">
		<input style="margin-top: 10px;" type="submit" value="Trainings erstellen!" />
	</div>

	<p class="warning">
		Achtung: Nach dem Absenden des Formulars werden die Trainings ohne weitere &Uuml;berpr&uuml;fung in die Datenbank eingetragen.
	</p>
</form>
<?php endif; ?>