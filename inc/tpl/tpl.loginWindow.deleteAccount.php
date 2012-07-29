<div class="w50" id="loginWindow">
		<fieldset>
			<legend>Account l&ouml;schen</legend>
		<?php if (AccountHandler::tryToDeleteAccount()): ?>
			<p class="info">
				Dein Account wurde erfolgreich gel&ouml;scht.<br />
				Du kannst Runalyze nun nutzen.
			</p>
		<?php else: ?>
			<p class="error">
				Der Account konnte nicht gel&ouml;scht werden.<br />
				Vermutlich war der Link ung&uuml;ltig.
			</p>
		<?php endif; ?>
		</fieldset>

		<p class="text">
			<a class="button" href="index.php" title="zu Runalyze">zur Startseite</a>
		</p>
</div>