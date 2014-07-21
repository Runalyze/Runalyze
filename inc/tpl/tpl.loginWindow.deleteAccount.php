<div class="w50" id="login-window">
		<fieldset>
			<legend><?php _e('Delete account'); ?></legend>
	<?php if (!isset($_GET['want'])): ?>   
			<p class="warning">
				<?php _e('Do you really want to delete your account?'); ?><br>
				<br>
				<a href="login.php?delete=<?php echo $_GET['delete']; ?>&want=true"><strong><?php _e('Delete account'); ?> &raquo;</strong></a>
	<?php else: ?>      
		<?php if (AccountHandler::tryToDeleteAccount()): ?>
			<p class="info">
				<?php _e('Your account has been deleted.'); ?><br>
				<?php _e('You cannot use Runalyze anymore.'); ?>
			</p>
		<?php else: ?>
			<p class="error">
				<?php _e('The deletion did not work.'); ?><br>
				<?php _e('Probably the link was wrong.'); ?>
			</p>
		<?php endif; ?>
	<?php endif; ?>  
                </fieldset>

		<p class="text">
			<a class="button" href="index.php" title="Runalyze">&raquo; <?php _e('Main page'); ?></a>
		</p>
</div>