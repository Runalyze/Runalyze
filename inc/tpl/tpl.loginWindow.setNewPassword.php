<div class="w50" id="login-window">
	<form action="login.php?chpw=<?php echo $_GET['chpw']; ?>" method="post">

<?php $errors = AccountHandler::tryToSetNewPassword(); ?>
<?php $user   = AccountHandler::getUsernameForChangePasswordHash(); ?>

		<fieldset>
			<legend><?php _e('Set new password'); ?></legend>
		<?php if ($user): ?>
			<input type="hidden" name="chpw_hash" value="<?php echo $_GET['chpw']; ?>">
			<input type="hidden" name="chpw_username" value="<?php echo $user; ?>">
			<div class="w100">
				<label for="chpw_name"><?php _e('Username'); ?></label>
				<input id="chpw_name" name="chpw_name" class="middle-size" type="text" value="<?php echo $user; ?>" disabled=>
			</div>
			<div class="w100">
				<label for="new_pw"><?php _e('New password'); ?></label>
				<input id="new_pw" name="new_pw" class="middle-size" type="password">
			</div>
			<div class="w100 clear">
				<label for="new_pw_again"><?php _e('New password again'); ?></label>
				<input id="new_pw_again" name="new_pw_again" class="middle-size" type="password">
			</div>
		<?php else: ?>
			<?php echo HTML::error( __('The link is not valid anymore.') ); ?>
		<?php endif; ?>
		<?php if (is_array($errors) && !empty($errors)) foreach ($errors as $error) echo HTML::error($error); ?>
		</fieldset>

		<div class="c">
			<input type="submit" value="<?php _e('Change password'); ?>" name="submit">
		</div>
	</form>
</div>
