<div class="w50" id="loginWindow">
	<div id="login">
		<form action="login.php" method="post">
	
			<fieldset>
				<legend>Login</legend>
				<div class="w100">
					<label for="username">Benutzer</label>
					<input id="username" name="username" class="middleSize withUnit unitUser" type="text" value="<?php if (isset($_POST['username'])) echo str_replace('"','',$_POST['username']); ?>" />
				</div>
				<div class="w100 clear">
					<label for="password">Passwort</label>
					<input id="password" name="password" class="middleSize withUnit unitPass" type="password" />
				</div>
				<div class="w100 clear">
					<label for="autologin" class="small">Eingeloggt bleiben</label>
					<input id="autologin" name="autologin" type="checkbox" />
				</div>
			</fieldset>

			<?php
			if (SessionHandler::$ErrorType != SessionHandler::$ERROR_TYPE_NO) {
				if (SessionHandler::$ErrorType == SessionHandler::$ERROR_TYPE_WRONG_USERNAME)
					echo '<p class="error">Der Benutzername ist nicht bekannt.</p>';
				elseif (SessionHandler::$ErrorType == SessionHandler::$ERROR_TYPE_WRONG_PASSWORD)
					echo '<p class="error">Das Passwort war nicht richtig.</p>';
			}
			?>

			<div class="c">
				<input type="submit" value="Login" name="submit" />
			</div>
		</form>
	</div>

	<div id="registerFormular" style="display:none;">
		<form action="login.php" method="post">
			<fieldset>
				<legend onclick="show('log');">Registrieren</legend>
			<?php
			FormularInput::setStandardSize(FormularInput::$SIZE_MIDDLE);

			$Field = new FormularInput('new_username', 'Benutzer');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->display();

			$Field = new FormularInput('name', 'Name');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->display();

			$Field = new FormularInput('email', 'E-Mail');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->display();

			$Field = new FormularInput('password', 'Passwort');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->display();

			$Field = new FormularInput('password_again', 'Passwort erneut');
			$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
			$Field->display();
			?>

			<?php
			if (isset($_POST['new_username'])) {
				$Errors = AccountHandler::tryToRegisterNewUser();
				if (is_array($Errors))
					foreach ($Errors as $Error)
						echo Html::error($Error);
				else
					echo Html::info('Danke f&uuml;r deine Anmeldung! Du solltest in den n&auml;chsten Minuten eine E-Mail erhalten, in der du den Account best&auml;tigen kannst.');
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
					<label for="send_username">Benutzer</label>
					<input id="send_username" name="send_username" class="middleSize withUnit unitUser" type="text" value="<?php if (isset($_POST['username'])) echo str_replace('"','',$_POST['username']); ?>" />
				</div>

			<?php if (isset($_POST['send_username'])): ?>
				<p class="small">
					<?php echo AccountHandler::sendPasswordLinkTo($_POST['send_username']); ?>
				</p>
			<?php else: ?>
				<p class="small">
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

<?php if (isset($_POST['new_username'])) echo Ajax::wrapJSforDocumentReady("show('reg');") ?>
<?php if (isset($_POST['send_username'])) echo Ajax::wrapJSforDocumentReady("show('pwf');") ?>