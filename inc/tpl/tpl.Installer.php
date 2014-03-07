	<p class="text c">
<?php
$Steps = array(1 => 'Start', 2 => 'Konfiguration', 3 => 'Datenbank', 4 => 'Fertig');
foreach ($Steps as $i => $Name) {
	$opacity = ($i == $this->currentStep) ? '1' : '0.5';

	echo '<strong style="opacity:'.$opacity.';">'.$Name.'</strong>';

	if ($i != 4)
		echo ' &nbsp;&nbsp;&raquo;&nbsp;&nbsp; ';
}
?>
	</p>

	
<?php if ($this->currentStep == self::$ALREADY_INSTALLED): ?>

	<p class="text-headline">
		Runalyze ist bereits installiert.
	</p>

	<p class="text">
		Der Assistent kann daher Runalyze nicht erneut installieren.<br />
		<br />
		Wenn Probleme mit der aktuellen Installation auftreten und etwas funktioniert, sollte zun&auml;chst die Datenbank gesichert werden, um die Daten nicht zu verlieren.
		Bei unbekannten Fehlern sollten diese im <a href="http://trac.runalyze.de/cgi-bin/trac.fcgi/newticket" title="Runalyze: Ticket-System">Ticket-System</a> gemeldet werden.
		Die Entwickler stehen auch gerne pers&ouml;nlich bereit, um Probleme zu beheben. 
	</p>

	<p class="text">
		<a class="button" href="index.php" title="zu Runalyze">zu Runalyze</a>
	</p>

	<p class="warning">
		F&uuml;r eine Neuinstallation muss die Konfigurationsdatei <em>config.php</em> (Startverzeichnis) gel&ouml;scht werden.
	</p>

<?php elseif ($this->currentStep == self::$START): ?>

<form action="install.php" method="post">
	<p class="text">
		<strong>Herzlichen Willkommen!</strong>
	</p>

	<p class="text">
		Sch&ouml;n dass du dich entschieden hast, mit Runalyze eines der mit Sicherheit innovativsten und individuellsten Lauftageb&uuml;cher zu nutzen.
		Dieser Assistent f&uuml;hrt dich durch die Installation.
		Daf&uuml;r brauchst du nichts weiter als einen laufenden <small>(meist lokalen)</small> Server mit PHP5 und MySQL5 sowie die Verbindungsdaten zur MySQL-Datenbank. 
	</p>

	<?php if (!$this->phpVersionIsOkay()): ?>
	<p class="error">
		Es wird mindestens PHP <?php echo self::$REQUIRED_PHP_VERSION; ?> ben&ouml;tigt. Derzeit l&auml;uft PHP <?php echo PHP_VERSION; ?>
	</p>
	<?php else: ?>
	<p class="okay">
		Derzeit l&auml;uft PHP <?php echo PHP_VERSION; ?>
	</p>
	<?php endif; ?>

	<p class="text">&nbsp;</p>

	<p class="text">
		Das Importieren von gro&szlig;en Dateien (lange Trainings oder mehrere, z.B. SportTracks-Logbook)
		kann rechenaufw&auml;ndig sein. Je nach Servereinstellungen kann es daher zu Problemen kommen,
		da nicht jeder Anbieter das Hochsetzen der Limits erlaubt.
	</p>

	<p class="info">Zeit-Limit: <?php echo ini_get('max_execution_time'); ?>s</p>
	<p class="info">Memory-Limit: <?php echo ini_get('memory_limit'); ?></p>
	<p class="info">Upload-Limit: <?php echo ini_get('upload_max_filesize'); ?></p>

	<p class="text">&nbsp;</p>

	<p class="text">
			<input type="hidden" name="step" value="2" />

			<input type="submit" value="Installation starten" />
	</p>
</form>

<?php elseif ($this->currentStep == self::$SETUP_CONFIG): ?>

<form action="install.php" method="post">
	<p class="text">
		<strong>Einstellungen f&uuml;r Runalyze</strong>
	</p>

	<p class="text">
		Damit Runalyze deine Trainings speichern kann, ist die Verbindung zu einer MySQL-Datenbank notwendig.
		Die Zugangsdaten k&ouml;nnen notfalls immer beim Administrator erfragt werden.
	</p>

	<?php if ($this->connectionIsIncorrect): ?>
		<p class="error">
			Die Verbindungsdaten sind falsch. Eine Verbindung konnte nicht hergestellt werden.
		</p>
	<?php else: ?>
		<p class="okay">
			Die Verbindung konnte hergestellt werden.
		</p>
	
		<?php if ($this->mysqlVersionIsOkay()): ?>
		<p class="okay">
			Es l&auml;uft MySQL <?php echo $this->getMysqlVersion(); ?>
		</p>
		<?php elseif (!$this->cantWriteConfig): ?>
		<p class="error">
			Es wird mindestens MySQL <?php echo self::$REQUIRED_MYSQL_VERSION; ?> ben&ouml;tigt. Derzeit l&auml;uft MySQL <?php echo $this->getMysqlVersion(); ?>
		</p>
		<?php endif; ?>
	<?php endif; ?>

	<p class="text">
		<label>
			<strong>Host-Server</strong>
			<input type="text" name="host" value="<?php echo (isset($_POST['host']) ? $_POST['host'] : 'localhost'); ?>" <?php if ($this->readyForNextStep) echo 'readonly="readonly"'; ?> />
		</label><br />
		<label>
			<strong>Datenbank</strong>
			<input type="text" name="database" value="<?php echo (isset($_POST['database']) ? $_POST['database'] : 'runalyze'); ?>" <?php if ($this->readyForNextStep) echo 'readonly="readonly"'; ?> />
		</label><br />
		<label>
			<strong>Benutzer</strong>
			<input type="text" name="username" value="<?php echo (isset($_POST['username']) ? $_POST['username'] : 'root'); ?>" <?php if ($this->readyForNextStep) echo 'readonly="readonly"'; ?> />
		</label><br />
		<label>
			<strong>Passwort</strong>
			<input type="password" name="password" value="<?php echo (isset($_POST['password']) ? $_POST['password'] : ''); ?>" <?php if ($this->readyForNextStep) echo 'readonly="readonly"'; ?> />
		</label><br />
	</p>

	<p class="text">
		Falls mehrere Versionen parallel laufen sollen, kann ein eigener Datenbank-Pr&auml;fix vergeben werden.
	</p>

	<?php if ($this->prefixIsAlreadyUsed): ?>
	<p class="error">
		Unter diesem Pr&auml;fix l&auml;uft bereits eine Installation.
	</p>
	<?php elseif (!$this->connectionIsIncorrect): ?>
	<p class="okay">
		Mit dem Pr&auml;fix l&auml;uft noch keine Installation.
	</p>
	<?php endif; ?>

	<p class="text">
		<label>
			<strong>Pr&auml;fix</strong>
			<input type="text" name="prefix" value="<?php echo (isset($_POST['prefix']) ? $_POST['prefix'] : 'runalyze_'); ?>" <?php if ($this->readyForNextStep) echo 'readonly="readonly"'; ?> />
		</label>
	</p>

	<p class="text">
		Wenn du selbst Entwickler bist oder uns beim Beheben von Fehlern helfen m&ouml;chtest,
		kannst du den Debug-Modus aktivieren. Bei auftretenden Problemen werden die Fehlermeldungen dann
		in einer Toolbar am unteren Bildschirmrand angezeigt.
		<small>(normalerweise aus)</small>
	</p>

	<p class="text">
		<label>
			<strong>Debug-Modus</strong>
			<input type="checkbox" name="debug" <?php if (isset($_POST['debug']) && $_POST['debug']) echo 'checked="checked"' ?> />
		</label>
	</p>

	<p class="text">
		<label>
			<strong>Anmeldungen</strong>
			<input type="checkbox" name="login" <?php if (isset($_POST['login']) && $_POST['login']) echo 'checked="checked"' ?> />
			<small>
				Benutzer m&uuml;ssen sich registrieren und einloggen
			</small>
		</label>
	</p>

	<p class="text">
		<label>
			<strong>Garmin API-Key*</strong>
			<input type="text" name="garminkey" value="<?php echo (isset($_POST['garminkey']) ? $_POST['garminkey'] : ''); ?>" />
			<?php if ($_SERVER['SERVER_NAME'] == 'localhost'): ?>
				<small>(f&uuml;r <em>localhost</em> nicht notwendig)</small>
			<?php else: ?>
				<small>
					(notwendig f&uuml;r <em><?php echo ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']; ?></em>,
					siehe <a href="http://developer.garmin.com/web-device/garmin-communicator-plugin/get-your-site-key/">developer.garmin.com</a>)
				</small>
			<?php endif; ?>
		</label>
	</p>

	<p class="text">
		<small>
			* Der Garmin API-Key ist notwendig, um den Garmin Communicator nutzen zu k&ouml;nnen,
			um Trainings direkt von einem Garmin Forerunner in Runalyze zu importieren.
		</small>
	</p>

	<?php if ($this->cantWriteConfig): ?>
	<p class="error">
		Die Konfigurations-Datei kann nicht geschrieben werden.<br />
	</p>

	<?php if (empty($this->writeConfigFileString)): ?>
	<p class="error">
		Bitte kopiere <strong>/runalyze/inc/install/config.php</strong> in das Hauptverzeichnis <strong>/runalyze/</strong> und trage die Verbindungsdaten von Hand ein.
		Dabei musst du folgende &Auml;nderungen vornehmen:<br />
		<em>'{config::host}'</em> &raquo; <em>'<?php echo $_POST['host']; ?>'</em><br />
		<em>'{config::database}'</em> &raquo; <em>'<?php echo $_POST['database']; ?>'</em><br />
		<em>'{config::username}'</em> &raquo; <em>'<?php echo $_POST['username']; ?>'</em><br />
		<em>'{config::password}'</em> &raquo; <em>'<?php echo $_POST['password']; ?>'</em><br />
		<em>'{config::prefix}'</em> &raquo; <em>'<?php echo $_POST['prefix']; ?>'</em><br />
		<em>{config::debug}</em> &raquo; <em><?php echo isset($_POST['debug']) ? 'true' : 'false'; ?></em><br />
		<em>{config::login}</em> &raquo; <em><?php echo isset($_POST['login']) ? 'true' : 'false'; ?><br />
		<em>{config::garminkey}</em> &raquo; <em><?php echo $_POST['garminkey']; ?><br />
	</p>
	<?php else: ?>
	<p class="error">
		Bitte speichere folgenden Code als <strong>/runalyze/config.php</strong>:
	</p>
	<textarea class="code"><?php echo htmlspecialchars($this->writeConfigFileString); ?></textarea>
	<?php endif; ?>
	<?php endif; ?>

	<p class="text">
		<?php if ($this->readyForNextStep || $this->cantWriteConfig): ?>
			<input type="hidden" name="write_config" value="true" />
		<?php endif; ?>
		<input type="hidden" name="step" value="2" />

		<input type="submit" value="<?php echo $this->cantWriteConfig ? 'Gespeichert! Weiter ...' : ( $this->readyForNextStep ? 'Konfigurationsdatei schreiben' : 'Verbindungsdaten pr&uuml;fen' ); ?>" />
	</p>
</form>

<?php elseif ($this->currentStep == self::$SETUP_DATABASE): ?>

<form action="install.php" method="post">
	<p class="text">
		Die <strong>Konfigurations-Datei</strong> wurde erfolgreich ins Verzeichnis geschrieben.
		Den Debug-Modus kann man dort sp&auml;ter auch manuell &auml;ndern.
	</p>

	<p class="text">
		Im folgenden Schritt wird die <strong>Datenbank</strong> bef&uuml;llt.
	</p>

	<textarea class="code"><?php echo $this->getSqlContentForFrontend('inc/install/structure.sql'); ?></textarea>

	<textarea class="code"><?php echo $this->getSqlContentForFrontend('inc/install/runalyze_empty.sql'); ?></textarea>

	<?php if ($this->cantSetupDatabase): ?>
	<p class="error">
		Die Datenbank kann nicht bef&uuml;llt werden.<br />
		Bitte importiere die beiden obigen Daten nacheinander in die Datenbank.<br />
		<br />
		Danach kannst du hier fortfahren.
	<?php endif; ?>

	<p class="text">
		<input type="hidden" name="step" value="3" />

		<input type="submit" value="Tabellen erstellen" />
	</p>
</form>

<?php elseif ($this->currentStep == self::$READY): ?>

	<p class="text">
		<strong>Herzlichen Gl&uuml;ckwunsch!</strong>
	</p>

	<p class="text">
		Runalyze wurde erfolgreich installiert.
		Und kann nun genutzt werden.
		Im ersten Schritt sollten aber einige Einstellungen vorgenommen werden.
	</p>

	<p class="text">
		Links oben findet sich der Link zur <strong>Konfiguration</strong>.
		Hier sollten zun&auml;chst die wichtigsten Einstellungen vorgenommen werden, damit alles ganz deinen W&uuml;nschen entspricht.
		Im Anschluss ist es empfehlenswert, sich ein wenig mit der Oberfl&auml;che vertraut zu machen und ein erstes Training einzutragen.
		Dies geschieht durch einen Klick auf das &quot;Hinzuf&uuml;gen&quot;-Zeichen, das sich im <em>Data-Browser</em> <small>(links oben)</small> oben rechts befindet.
		Trainings k&ouml;nnen sowohl hochgeladen als auch manuell eingegeben werden.
	</p>

	<p class="text">
		Viel Spa&szlig; mit Runalyze!
	</p>

	<p class="text">
		<a class="button" href="index.php" title="zu Runalyze">Runalyze starten</a>
	</p>

<?php
$CHMOD_FOLDERS = array();
include PATH.'system/define.chmod.php';

foreach ($CHMOD_FOLDERS as $folder)
	@chmod(PATH.'../'.$folder, 0777);

clearstatcache();

foreach ($CHMOD_FOLDERS as $folder) {
	$realfolder = PATH.'../'.$folder;

	if (!is_writable($realfolder))
		echo '<p class="error">Das Verzeichnis <strong>'.$folder.'</strong> ist nicht beschreibbar. <em>(chmod = '.substr(decoct(fileperms($realfolder)),1).')</em></p>';
}
?>

<?php endif; ?>

	<noscript>
		<p class="error" id="JSerror">
			JavaScript ist deaktiviert. Ohne JavaScript wird Runalyze nicht funktionieren!
		</p>
	</noscript>

<?php
$URLs = array(
	System::getFullDomain().'lib/min/?g=js',
	System::getFullDomain().'lib/min/?g=css'
);
?>
	<p class="error" id="JQueryError">
		Die JavaScript-Dateien (und CSS-Dateien) wurden nicht erfolgreich eingebunden.<br />
		Die Folgenden URLs m&uuml;ssen den entsprechenden Code liefern:<br />
		<br />
		<?php
		foreach ($URLs as $URL)
			echo '<em><a href="'.$URL.'">'.$URL.'</a></em><br />';
		?>
		<br />
		Schaue am besten einmal in unsere <a href="http://runalyze.de/faq/">FAQ</a> und berichte uns ggf. von deinen Problemen.
	</p>

	<script type="text/javascript">$(document).ready(function(){ $("#JQueryError").remove(); });</script>

	<p class="text">
		&nbsp;
	</p>