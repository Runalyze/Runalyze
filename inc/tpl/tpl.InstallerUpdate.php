<form action="update.php" method="post">
	<p class="text">
		<strong><?php _e('Update RUNALYZE'); ?></strong>
	</p>

	<p class="text">
		<?php _e('After downloading the latest version of RUNALYZE you need to update your database, '.
				'as we improve our database structure and add new features with every update. '.
				'If you have skipped a version, you need to apply the respective updates in the correct order.'); ?>
	</p>

	<p class="info">
		<?php printf(__('Detailed upgrade instructions: %s'), '<a href="http://docs.runalyze.com/en/latest/update.html" target="_blank">docs.runalyze.com/en/latest/update.html</a>'); ?>
	</p>

	<?php if (!$this->triesToUpdate()): ?>

		<p class="text">
			&nbsp;
		</p>

		<p class="warning">
			<?php _e('We urgently recommend a backup of your existing database prior to any update of RUNALYZE.'); ?>
		</p>

		<?php if (!$this->CacheWasCleared): ?>
		<p class="warning">
			<?php _e('Trying to clear your cache failed.'); ?>
		</p>
		<?php endif; ?>

		<p class="text">
			&nbsp;
		</p>

		<?php if (!$this->installationHasAccounts()): ?>

			<p class="error">
				<?php printf(
					__('Single-user installations are not supported anymore. '.
					'Please register a new account by following %s.'),
					'<a href="http://docs.runalyze.com/en/latest/update.html#upgrade-single-user-installation" target="_blank">'.__('these instructions').'</a>'
				); ?>
			</p>

		<?php else: ?>

			<p class="text">
				<select name="importFile">
					<option value="-1"><?php _e('----- please choose'); ?></option>
					<?php foreach ($this->PossibleUpdates as $i => $Update): ?>
					<option value="<?php echo $i; ?>"><?php echo sprintf( __('Update to %s (from %s, %s)'), $Update['to'], $Update['from'], $Update['date']); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
	
			<p class="text">
				<input type="submit" value="<?php _e('Update RUNALYZE'); ?>">
			</p>

		<?php endif; ?>

	<?php else: ?>

		<p class="text">
			&nbsp;
		</p>

		<?php if ($this->hasErrors()): ?>
			<p class="error">
				<?php echo implode('<br>', $this->Errors); ?>
			</p>
		<?php elseif (isset($_POST['importFile'])): ?>
			<p class="okay">
				<?php _e('Your database has been updated successfully.'); ?>
			</p>

			<?php if (empty($this->FurtherInstructions)): ?>
				<p class="text">
					&nbsp;
				</p>

				<p class="text">
					<a class="button" href="index.php" title="Runalyze"><?php _e('Start RUNALYZE'); ?></a>
				</p>
			<?php else: ?>
				<?php foreach ($this->FurtherInstructions as $message): ?>
					<p class="warning">
						<?php echo $message; ?>
					</p>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endif; ?>

	<?php endif; ?>

	<p class="text">
		&nbsp;
	</p>

</form>
