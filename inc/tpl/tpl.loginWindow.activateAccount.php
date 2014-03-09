<div class="w50" id="login-window">
		<fieldset>
			<legend>Account aktivieren</legend>
		<?php if (AccountHandler::tryToActivateAccount()): ?>
			<p class="info">
				Dein Account wurde erfolgreich aktiviert.<br>
				Du kannst Runalyze nun nutzen.
			</p>
		<?php else: ?>
			<p class="error">
				Die Aktivierung hat nicht geklappt.<br>
				Vermutlich war der Link ung&uuml;ltig.
			</p>
		<?php endif; ?>
		</fieldset>

		<p class="text">
			<a class="button" href="login.php" title="zu Runalyze">zum Login</a>
		</p>
</div>