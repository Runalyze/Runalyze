<div class="w50" id="loginWindow">
		<fieldset>
			<legend>Account l&ouml;schen</legend>
                <?php if($_GET['want'] != true): ?>   
                                                <p class="info">
                            M&ouml;chtest du deinen Account wirklich l&ouml;schen?.<br />
                            <a href="login.php?delete=<?php echo $_GET['delete']; ?>&want=true">Dann klicke auf diesen Link und dein Account wird unwiederuflich gel&ouml;scht!</a>
                <?php else: ?>      
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
                    <?php endif; ?>  
                </fieldset>

		<p class="text">
			<a class="button" href="index.php" title="zu Runalyze">zur Startseite</a>
		</p>
</div>