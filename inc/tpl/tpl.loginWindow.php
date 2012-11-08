<div class="w50" id="loginWindow">
	<div id="login">
		<form action="login.php" method="post">

			<?php
			$ErrorString = '';
			$FailedUsername = '';
			$FailedPassword = '';
			if (SessionAccountHandler::$ErrorType != SessionAccountHandler::$ERROR_TYPE_NO) {
				if (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_WRONG_USERNAME) {
					$ErrorString    = 'Der Benutzername ist nicht bekannt.';
					$FailedUsername = ' validationFailed';
				} elseif (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_WRONG_PASSWORD) {
					$ErrorString    = 'Das Passwort war nicht richtig.';
					$FailedPassword = ' validationFailed';
				} elseif (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_ACTIVATION_NEEDED)
					$ErrorString    = 'Der Account wurde noch nicht best&auml;tigt.<br />Schau in deinem E-Mail-Posteingang nach.';
			}
			?>

			<fieldset>
				<legend>Login</legend>
				<div class="w100">
					<label for="username">Benutzername</label>
					<input id="username" name="username" class="middleSize withUnit unitUser <?php echo $FailedUsername; ?>" type="text" value="<?php if (isset($_POST['username'])) echo str_replace('"','',$_POST['username']); ?>" />
				</div>
				<div class="w100 clear">
					<label for="password">Passwort</label>
					<input id="password" name="password" class="middleSize withUnit unitPass <?php echo $FailedPassword; ?>" type="password" />
				</div>
				<div class="w100 clear">
					<label for="autologin" class="small">Eingeloggt bleiben</label>
					<input id="autologin" name="autologin" type="checkbox" />
				</div>
			</fieldset>

			<?php if (!empty($ErrorString)) echo HTML::error($ErrorString); ?>

			<div class="c">
				<input type="submit" value="Einloggen" name="submit" />
			</div>
		</form>
	</div>

	<div id="registerFormular" style="display:none;">
		<form action="login.php" method="post">
			<fieldset>
				<legend onclick="show('log');">Registrieren</legend>
			<?php
			if (isset($_POST['new_username'])) {
				$Errors = AccountHandler::tryToRegisterNewUser();

				if (is_array($Errors))
					foreach ($Errors as $Error)
						if (is_array($Error))
							foreach (array_keys($Error) as $FieldName)
								FormularField::setKeyAsFailed($FieldName);
			}

			FormularInput::setStandardSize(FormularInput::$SIZE_MIDDLE);

			$Field = new FormularInput('new_username', 'Benutzername');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->setUnit( FormularUnit::$USER );
			$Field->display();

			$Field = new FormularInput('name', 'Name');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->setUnit( FormularUnit::$USER );
			$Field->display();

			$Field = new FormularInput('email', 'E-Mail');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->setUnit( FormularUnit::$MAIL );
			$Field->display();
			
			$Field = new FormularInputPassword('password', 'Passwort');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->setUnit( FormularUnit::$PASS );
			$Field->display();

			$Field = new FormularInputPassword('password_again', 'Passwort erneut');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->setUnit( FormularUnit::$PASS );
			$Field->display();
			?>

			<?php
			if (isset($_POST['new_username'])) {
				if (is_array($Errors))
					foreach ($Errors as $Error) {
						if (is_array($Error))
							foreach ($Error as $String)
								echo HTML::error($String);
						else
							echo HTML::error($Error);
					}
				else
					echo HTML::info('Danke f&uuml;r deine Anmeldung! Du solltest in den n&auml;chsten Minuten eine E-Mail erhalten, in der du den Account best&auml;tigen kannst.');
			}
			?>
			</fieldset>

			<div class="c">
				<input type="submit" value="Registrieren" name="submit" />
			</div>
		</form>
	</div>

	<div id="forgotPassword" style="display:none;">
		<form action="login.php" method="post">
			<fieldset>
				<legend onclick="show('log');">Passwort vergessen</legend>
				<div class="w100">
					<label for="send_username">Benutzername</label>
					<input id="send_username" name="send_username" class="middleSize withUnit unitUser" type="text" value="<?php if (isset($_POST['username'])) echo str_replace('"','',$_POST['username']); ?>" />
				</div>

			<?php if (isset($_POST['send_username'])): ?>
				<p class="info small">
					<?php echo AccountHandler::sendPasswordLinkTo($_POST['send_username']); ?>
				</p>
			<?php else: ?>
				<p class="info small">
					Ein Link zum &Auml;ndern deines Passworts wird an deine eingetragene E-Mail-Adresse gesendet.
				</p>
			<?php endif; ?>
			</fieldset>

			<div class="c">
				<input type="submit" value="Link zusenden" name="submit" />
			</div>
		</form>
	</div>
</div>

<div id="loginPanel">
	<a id="loginLink" href="#" onclick="show('log')">Login</a>
	<a id="registerLink" href="#" onclick="show('reg')">Registrieren</a>
	<a id="passLink" href="#" onclick="show('pwf');">Passwort vergessen?</a>
</div>

<script type="text/javascript">
function show(what) {
	var $log = $("#login"), $reg = $("#registerFormular"), $pwf = $("#forgotPassword");
	if (what == 'reg') { $reg.show(); $log.hide(); $pwf.hide();	}
	else if (what == 'pwf') { $pwf.show(); $reg.hide(); $log.hide(); }
	else if (what == 'log') { $log.show(); $pwf.hide(); $reg.hide(); }
}
</script>

<?php
$NumUserOn = SessionAccountHandler::getNumberOfUserOnline();
$NumUser   = Mysql::getInstance()->untouchedFetch('SELECT COUNT(*) as num FROM '.PREFIX.'account');
$NumKm     = Mysql::getInstance()->untouchedFetch('SELECT SUM(distance) as num FROM '.PREFIX.'training');

$sindOn = ($NumUserOn == 1) ? 'ist' : 'sind';
$sind   = ($NumUser['num'] == 1) ? 'ist' : 'sind';
$haben  = ($NumUser['num'] == 1) ? 'hat' : 'haben';
?>

<p class="text"></p>
<p class="text small c">
	Bisher <?php echo $sind; ?> <strong><?php echo $NumUser['num']; ?></strong> L&auml;ufer bei uns angemeldet
	und <?php echo $haben; ?> <strong><?php echo Running::Km($NumKm['num']); ?></strong> eingetragen.
	<br />
	Davon <?php echo $sindOn; ?> derzeit <strong><?php echo $NumUserOn; ?></strong> L&auml;ufer online.
</p>

<?php if (isset($_POST['new_username'])) echo Ajax::wrapJSforDocumentReady("show('reg');") ?>
<?php if (isset($_POST['send_username'])) echo Ajax::wrapJSforDocumentReady("show('pwf');") ?>