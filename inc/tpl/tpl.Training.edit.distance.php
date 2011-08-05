<?php if ($Training->hasType()) echo Type::getSelectBox(); ?>
<br />

<?php if ($Training->Sport()->isRunning()): ?>
	<?php echo Shoe::getSelectBox(); ?>
	<br />

	<label>
		<?php echo HTML::checkBox('abc'); ?>
		Lauf-ABC
	</label><br />
<?php endif; ?>

<?php if ($Training->Sport()->usesPulse()): ?>
	<label>
		<?php echo HTML::simpleInputField('pulse_avg', 3); ?>
		<small>&oslash; Puls</small>
	</label><br />
	<label>
		<?php echo HTML::simpleInputField('pulse_max', 3); ?>
		<small>max. Puls</small>
	</label><br />
<?php endif; ?>

<?php if ($Training->Type()->hasSplits()): ?>
	<label>
		<?php echo HTML::textarea('splits', 70, 3); ?>
		<small>Splits</small>
	</label><br />
<?php endif; ?>