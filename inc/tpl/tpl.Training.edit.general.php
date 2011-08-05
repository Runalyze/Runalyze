<?php echo HTML::hiddenInput('sportid'); ?>
<?php echo HTML::hiddenInput('s_old'); ?>
<?php echo HTML::hiddenInput('dist_old'); ?>
<?php echo HTML::hiddenInput('shoeid_old'); ?>

<label>
	<?php echo HTML::disabledInputField('sport'); ?>
	<small>Sport</small>
</label>
<br />

<label>
	<?php echo HTML::simpleInputField('datum', 10); ?>
	<?php echo HTML::simpleInputField('zeit', 4); ?>
	<small>Datum</small>
</label>
<br />

<?php echo HTML::hiddenInput('kcalPerHour'); ?>

<label>
	 <?php echo HTML::simpleInputField('s', 9); ?>
	<small>Dauer</small>
</label>

<label>
	 <?php echo HTML::simpleInputField('kcal', 4); ?>
	<small>kcal</small>
</label><br />

<?php if ($Training->Sport()->usesDistance()): ?>
	<label>
		<?php echo HTML::checkBox('is_track'); ?>
		<small>Bahn</small>
	</label>

	<label>
		 <?php echo HTML::simpleInputField('distance', 4); ?>
		<small>km</small>
	</label>
	<br />

	<label>
		<?php echo HTML::disabledInputField('pace', 3); ?>
		<small>/km</small>
	</label>

	<label>
		<?php echo HTML::disabledInputField('kmh', 3); ?>
		<small>km/h</small>
	</label>
	<br />
<?php endif; ?>

<label>
	<?php echo HTML::simpleInputField('comment', 50); ?>
	<small>Bemerkung</small><br />
</label>
<label>
	<?php echo HTML::simpleInputField('partner', 50); ?>
	<small>Trainingspartner</small>
</label>