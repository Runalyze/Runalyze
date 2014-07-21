<div class="w50" id="login-window">
		<fieldset>
			<legend><?php _e('Activate account'); ?></legend>
		<?php if (AccountHandler::tryToActivateAccount()): ?>
			<p class="info">
				<?php _e('Your account has been activated.'); ?><br>
				<?php _e('You can now use Runalyze.'); ?>
			</p>
		<?php else: ?>
			<p class="error">
				<?php _e('The activation did not work.'); ?><br>
				<?php _e('Probably the link was wrong.'); ?>
			</p>
		<?php endif; ?>
		</fieldset>

		<p class="text">
			<a class="button" href="login.php" title="Runalyze">&raquo; <?php _e('Login'); ?></a>
		</p>
</div>