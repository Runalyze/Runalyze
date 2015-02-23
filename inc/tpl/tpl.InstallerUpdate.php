<form action="update.php" method="post">
	<p class="text">
		<strong><?php _e('Update Runalyze'); ?></strong>
	</p>

	<p class="text">
		<?php _e('After downloading the latest version of Runalyze please update your database.'); ?>
		<?php _e('Therefore, choose the update in the following list. Afterwards you can use Runalyze as before.'); ?>
	</p>

	<p class="text">
		<?php _e('To be safe we recommend you to make a complete backup of your database.'); ?>
	</p>

	<p class="text">
		<select name="importFile">
			<option value=""><?php _e('----- please choose'); ?></option>
			<?php foreach ($this->PossibleUpdates as $Update): ?>
			<option value="<?php echo $Update['file']; ?>"><?php echo sprintf( __('Update to %s (from %s, %s)'), $Update['to'], $Update['from'], $Update['date']); ?></option>
			<?php endforeach; ?>
		</select>
	</p>

	<?php if (!empty($this->Errors) && (count($this->Errors) > 1 || strlen(trim($this->Errors[0])) > 3)): ?>
	<p class="error">
		<?php echo implode('<br>', $this->Errors); ?>
	</p>
	<?php elseif (isset($_POST['importFile'])): ?>
	<p class="info">
		<?php _e('The update was successful.'); ?>
	</p>
	<?php endif; ?>

	<p class="text">
		<input type="submit" value="<?php _e('Update'); ?>">
	</p>

	<p class="text">
		<a class="button" href="index.php" title="Runalyze"><?php _e('Start Runalyze'); ?></a>
	</p>
</form>