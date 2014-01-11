<div class="w50" id="loginWindow">
	<div id="login">
		<form action="login.php" method="post">

			<?php
			$ErrorString = '';
			$FailedUsername = '';
			$FailedPassword = '';
			if (SessionAccountHandler::$ErrorType != SessionAccountHandler::$ERROR_TYPE_NO) {
				if (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_WRONG_USERNAME) {
					$ErrorString    = __('Der Benutzername ist nicht bekannt.');
					$FailedUsername = ' validationFailed';
				} elseif (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_WRONG_PASSWORD) {
					$ErrorString    = __('Das Passwort war nicht richtig.');
					$FailedPassword = ' validationFailed';
				} elseif (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_ACTIVATION_NEEDED)
					$ErrorString    = __('Der Account wurde noch nicht best&auml;tigt.<br />Schau in deinem E-Mail-Posteingang nach.');
			}
			?>

			<?php if (!USER_CANT_LOGIN): ?>
			<fieldset>
				<legend><?php _e('Login'); ?></legend>
				<div class="w100">
					<label for="username"><?php _e('Username'); ?></label>
					<input id="username" name="username" class="middleSize withUnit unitUser <?php echo $FailedUsername; ?>" type="text" value="<?php if (isset($_POST['username'])) echo str_replace('"','',$_POST['username']); ?>" />
				</div>
				<div class="w100 clear">
					<label for="password"><?php _e('Password'); ?></label>
					<input id="password" name="password" class="middleSize withUnit unitPass <?php echo $FailedPassword; ?>" type="password" />
				</div>
				<div class="w100 clear">
					<label for="autologin" class="small"><?php _e('Remember me'); ?></label>
					<input id="autologin" name="autologin" type="checkbox" />
				</div>
			</fieldset>

			<?php if (!empty($ErrorString)) echo HTML::error($ErrorString); ?>

			<div class="c">
				<input type="submit" value="<?php _e('Login'); ?>" name="submit" />
			</div>
			<?php else: ?>
			<fieldset>
				<legend><?php _e('Login'); ?></legend>
				<p class="error"><?php _e('Runalyze is under maintanence at the moment. No login possible.'); ?></p>
			<?php endif; ?>
		</form>
	</div>

	<div id="registerFormular" style="display:none;">
		<form action="login.php" method="post">
			<fieldset>
				<legend onclick="show('log');"><?php _e('Register'); ?></legend>
			<?php
			if (!USER_CAN_REGISTER) {
				echo HTML::error(_e('At the moment registration is disabled.'));
			} else {
				if (isset($_POST['new_username'])) {
					$Errors = AccountHandler::tryToRegisterNewUser();

					if (is_array($Errors))
						foreach ($Errors as $Error)
							if (is_array($Error))
								foreach (array_keys($Error) as $FieldName)
									FormularField::setKeyAsFailed($FieldName);
				}

				FormularInput::setStandardSize(FormularInput::$SIZE_MIDDLE);

				$Field = new FormularInput('new_username', __('Username'));
				$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
				$Field->setUnit( FormularUnit::$USER );
				$Field->display();

				$Field = new FormularInput('name', __('Name'));
				$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
				$Field->setUnit( FormularUnit::$USER );
				$Field->display();

				$Field = new FormularInput('email', __('Email'));
				$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
				$Field->setUnit( FormularUnit::$MAIL );
				$Field->display();

				$Field = new FormularInputPassword('password', __('Password'));
				$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
				$Field->setUnit( FormularUnit::$PASS );
				$Field->display();

				$Field = new FormularInputPassword('password_again', __('Retype password'));
				$Field->setLayout(FormularFieldset::$LAYOUT_FIELD_W100);
				$Field->setUnit( FormularUnit::$PASS );
				$Field->display();
			}
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
				elseif (System::isAtLocalhost())
					echo HTML::info(_e('You can now login. Enjoy Runalyze!'));
				else
					echo HTML::info(_e('Thank you for your registration. You should receive a mail within the next minutes, where you have to activat your account.'));
			}
			?>
			</fieldset>

			<?php if (USER_CAN_REGISTER): ?>
			<div class="c">
				<input type="submit" value="<?php _e('Register'); ?>" name="submit" />
			</div>
			<?php endif; ?>
		</form>
	</div>

	<div id="forgotPassword" style="display:none;">
		<form action="login.php" method="post">
			<fieldset>
				<legend onclick="show('log');"><?php _e('Forgot password'); ?></legend>
				<div class="w100">
					<label for="send_username"><?php _e('Username'); ?></label>
					<input id="send_username" name="send_username" class="middleSize withUnit unitUser" type="text" value="<?php if (isset($_POST['username'])) echo str_replace('"','',$_POST['username']); ?>" />
				</div>

			<?php if (isset($_POST['send_username'])): ?>
				<p class="info small">
					<?php echo AccountHandler::sendPasswordLinkTo($_POST['send_username']); ?>
				</p>
			<?php else: ?>
				<p class="info small">
					<?php _e('Ein Link zum &Auml;ndern deines Passworts wird an deine eingetragene E-Mail-Adresse gesendet.'); ?>
				</p>
			<?php endif; ?>
			</fieldset>

			<div class="c">
				<input type="submit" value="<?php _e('Send link'); ?>" name="submit" />
			</div>
		</form>
	</div>
</div>

<div id="loginPanel">
	<a id="loginLink" href="#" onclick="show('log')"><?php _e('Login'); ?></a>
	<a id="registerLink" href="#" onclick="show('reg')"><?php _e('Register'); ?></a>
	<a id="passLink" href="#" onclick="show('pwf');"><?php _e('Forgot password?'); ?></a>
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

?>

<p class="text"></p>
<p class="text small c login-window-stats">
	<?php printf(_n('Until now <strong>%d</strong> athlete is registered and','Until now <strong>%d</strong> athletes are registered and', $NumUser['num']), $NumUser['num']); ?>
	<?php printf(_n('has logged <strong>%s</strong>.','have logged <strong>%s</strong>.', $NumUser['num']), Running::Km($NumKm['num'])); ?>
	<br />
	<?php printf(_n('<strong>%d</strong> athlete is online.','<strong>%d</strong> athletes are online.', $NumUserOn), $NumUserOn); ?><br>
</p>

<?php if (isset($_POST['new_username'])) echo Ajax::wrapJSforDocumentReady("show('reg');") ?>
<?php if (isset($_POST['send_username'])) echo Ajax::wrapJSforDocumentReady("show('pwf');") ?>