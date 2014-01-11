<div class="w50" id="login-window">
		<fieldset>
			<legend>Account l&ouml;schen</legend>
	<?php if (!isset($_GET['want'])): ?>   
			<p class="warning">
				M&ouml;chtest du deinen Account wirklich l&ouml;schen?<br />
				<br />
				<a href="login.php?delete=<?php echo $_GET['delete']; ?>&want=true"><strong>Account endg&uuml;ltig l&ouml;schen &raquo;</strong></a>
	<?php else: ?>      
		<?php if (AccountHandler::tryToDeleteAccount()): ?>
			<p class="info">
				Dein Account wurde erfolgreich gel&ouml;scht.<br />
				Du kannst Runalyze nun nicht mehr nutzen.
			</p>
		<?php else: ?>
			<p class="error">
				Der Account konnte nicht gel&ouml;scht werden.<br />
				Vermutlich war der Link ung&uuml;ltig.
			</p>
		<?php endif; ?>
	<?php endif; ?>  
                </fieldset>

		<p class="text">
			<a class="button" href="index.php" title="zu Runalyze">zur Startseite</a>
		</p>
</div>