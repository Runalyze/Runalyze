<label>
	<?php echo HTML::simpleInputField('route', 50); ?>
	<small style="margin-right: 63px;">Strecke</small>
</label>

<label>
	<?php echo HTML::simpleInputField('elevation', 3); ?>
	<small>HM</small>
</label>
<br />

<label>
	<?php echo Weather::getSelectBox(); ?>
	<small>Wetter</small>
</label>
<br />

<label>
	<?php echo HTML::simpleInputField('temperature', 2); ?>
	<small>&#176;C</small>
</label>
<br />

<br />

<small>Kleidung</small>
<br />
<?php echo Clothes::getCheckboxes(); ?>