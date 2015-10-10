<?php
use Runalyze\Activity\Distance;
?>
<div class="w50" id="login-window">
	<div id="login">
		<form action="login.php" method="post">

			<?php
			$ErrorString = '';
			$FailedUsername = '';
			$FailedPassword = '';
			if (SessionAccountHandler::$ErrorType != SessionAccountHandler::$ERROR_TYPE_NO) {
				if (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_WRONG_USERNAME) {
					$ErrorString    = __('The username is not known.');
					$FailedUsername = ' validation-failed';
				} elseif (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_WRONG_PASSWORD) {
					$ErrorString    = __('The password was incorrect.');
					$FailedPassword = ' validation-failed';
				} elseif (SessionAccountHandler::$ErrorType == SessionAccountHandler::$ERROR_TYPE_ACTIVATION_NEEDED)
					$ErrorString    = __('The account has not been activated.<br>Have a look into your email inbox.');
			}
			?>

			<?php if (!USER_CANT_LOGIN): ?>
			<fieldset>
				<legend><?php _e('Login'); ?></legend>
				<div class="w100">
					<label for="username"><?php _e('Username'); ?></label>
					<input id="username" name="username" class="middle-size <?php echo $FailedUsername; ?>" type="text" value="<?php if (isset($_POST['username'])) echo str_replace('"','',$_POST['username']); ?>">
				</div>
				<div class="w100 clear">
					<label for="password"><?php _e('Password'); ?></label>
					<input id="password" name="password" class="middle-size <?php echo $FailedPassword; ?>" type="password">
				</div>
				<div class="w100 clear">
					<label for="autologin" class="small"><?php _e('Remember me'); ?></label>
					<input id="autologin" name="autologin" type="checkbox">
				</div>
			</fieldset>

			<?php if (!empty($ErrorString)) echo HTML::error($ErrorString); ?>

			<div class="c">
				<input type="submit" value="<?php _e('Login'); ?>" name="submit">
			</div>
			<?php else: ?>
			<fieldset>
				<legend><?php _e('Login'); ?></legend>
				<p class="error"><?php _e('Runalyze is under maintenance at the moment. No login possible.'); ?></p>
			<?php endif; ?>
		</form>
	</div>

	<div id="registerFormular" style="display:none;">
		<form action="login.php" method="post">
			<fieldset>
				<legend onclick="show('log');"><?php _e('Create a new account'); ?></legend>
			<?php
			if (!USER_CAN_REGISTER) {
				echo HTML::error( __('Registrations are currently disabled.') );
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

				$Field = new FormularInputPassword('password_again', __('Password again'));
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
					echo HTML::info( __('You can login now. Enjoy Runalyze!') );
				else
					echo HTML::info( __('Thanks for your registration. You should receive an email within the next minutes with further instructions for activating your account.') );
			}
			?>
			</fieldset>

			<?php if (USER_CAN_REGISTER): ?>
			<div class="c">
				<input type="submit" value="<?php _e('Register'); ?>" name="submit">
			</div>
			<?php endif; ?>
		</form>
	</div>

	<div id="forgotPassword" style="display:none;">
		<form action="login.php" method="post">
			<fieldset>
				<legend onclick="show('log');"><?php _e('Forgot your password?'); ?></legend>
				<div class="w100">
					<label for="send_username"><?php _e('Username'); ?></label>
					<input id="send_username" name="send_username" class="middle-size" type="text" value="<?php if (isset($_POST['username'])) echo str_replace('"','',$_POST['username']); ?>">
				</div>

			<?php if (isset($_POST['send_username'])): ?>
				<p class="info small">
					<?php echo AccountHandler::sendPasswordLinkTo($_POST['send_username']); ?>
				</p>
			<?php else: ?>
				<p class="info small">
					<?php _e('A link for changing your password will be sent via email.'); ?>
				</p>
			<?php endif; ?>
			</fieldset>

			<div class="c">
				<input type="submit" value="<?php _e('Send link'); ?>" name="submit">
			</div>
		</form>
	</div>
</div>

<div id="login-panel">
	<a id="login-link" href="#" onclick="show('log')"><i class="fa fa-fw fa-lg fa-sign-in"></i> <?php _e('Login'); ?></a>
	<a id="register-link" href="#" onclick="show('reg')"><i class="fa fa-fw fa-lg fa-user"></i> <?php _e('Create a new account'); ?></a>
	<a id="password-link" href="#" onclick="show('pwf');"><i class="fa fa-fw fa-lg fa-lock"></i> <?php _e('Forgot your password?'); ?></a>
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
DB::getInstance()->stopAddingAccountID();

$NumUser = Cache::get('NumUser', 1);
if ($NumUser == null) {
    $NumUser = DB::getInstance()->query('SELECT COUNT(*) FROM '.PREFIX.'account WHERE activation_hash = ""')->fetchColumn();
    Cache::set('NumUser', $NumUser, '500', 1);
}

$NumKm = Cache::get('NumKm', 1);
if ($NumKm == null) {
    $NumKm = DB::getInstance()->query('SELECT SUM(distance) FROM '.PREFIX.'training')->fetchColumn();
    Cache::set('NumKm', $NumKm, '500', 1);
}
DB::getInstance()->startAddingAccountID();

$NumUserOn = SessionAccountHandler::getNumberOfUserOnline();
?>

<p class="text"></p>
<p class="text small c login-window-stats">
	<?php printf(_n('Until now <strong>%d</strong> athlete is registered and','Until now <strong>%d</strong> athletes are registered and', $NumUser), $NumUser); ?>
	<?php printf(_n('has logged <strong>%s</strong>.','have logged <strong>%s</strong>.', $NumUser), Distance::format($NumKm)); ?>
	<br>
	<?php printf(_n('<strong>%d</strong> athlete is online.','<strong>%d</strong> athletes are online.', $NumUserOn), $NumUserOn); ?><br>
</p>

<?php if (isset($_POST['new_username'])) echo Ajax::wrapJSforDocumentReady("show('reg');") ?>
<?php if (isset($_POST['send_username'])) echo Ajax::wrapJSforDocumentReady("show('pwf');") ?>
