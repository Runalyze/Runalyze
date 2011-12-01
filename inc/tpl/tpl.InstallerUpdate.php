<form action="install.php" method="post">
	<p class="text">
		<strong>Runalyze updaten:</strong>
	</p>

	<p class="text">
		Zusammen mit dieser Datei solltest du bereits im Besitz der neuen Runalyze-Version sein.
		Damit du deine bisherigen Daten weiter nutzen kannst, ist ein Update der Datenbank notwendig.
		Dazu kannst du im Folgenden das gew&uuml;nschte Update ausw&auml;hlen. 
	</p>

	<p class="text">
		Zur Sicherheit solltest du ein <strong>Datenbank-Backup</strong> anlegen.<br />
		<strong>Bitte geh sicher</strong>, welche Version du bisher hattest!
		Wenn du ein falches Update ausw&auml;hlst, kann das die Datenbank unwiderruflich ver&auml;ndern, sodass eine Neuinstallation notwendig w&auml;re.
	</p>

	<p class="text">
		<select name="importFile">
		<?php foreach ($this->PossibleUpdates as $Update): ?>
			<option value="<?php echo $Update['file']; ?>"><?php echo $Update['text']; ?></option>
		<?php endforeach; ?>
		</select>
	</p>

	<?php if (!empty($this->Errors)): ?>
	<p class="error">
		<?php implode('<br />', $this->Errors); ?>
	</p>
	<?php endif; ?>

	<p class="text">
		<input type="submit" value="Update" />
	</p>

	<p class="text">
		<a class="button" href="index.php" title="zu Runalyze">Runalyze starten</a>
	</p>
</form>