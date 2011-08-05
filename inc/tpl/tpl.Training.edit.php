<h1><?php $Training->displayTitle(true); ?>, <?php $Training->displayDate(); ?></h1>

<?php
if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>?id=<?php echo $id; ?>" id="training" onsubmit="return false;" method="post">

<input type="hidden" name="type" value="training" />
<input type="hidden" name="id" value="<?php echo $Training->get('id'); ?>" />

<?php
echo Ajax::change(Icon::get(Icon::$CROSS, 'Training l&ouml;schen'), 'edit-div', '#delete', 'right').NL;

if ($Training->isOutside())
	echo '<span class="right">&nbsp;|&nbsp;</span> '.Ajax::change('GPS-Daten', 'edit-div', '#edit-gps', 'right').NL;

echo Ajax::change('Allgemeines', 'edit-div', '#edit-general').NL;

if ($Training->Sport()->usesDistance())
	echo ' | '.Ajax::change('Training', 'edit-div', '#edit-train').NL;

if ($Training->isOutside())
	echo ' | '.Ajax::change('Outside', 'edit-div', '#edit-out').NL;
?>

<hr />

<div id="edit-div">
	<div id="delete" class="change" style="display:none;">
		<?php include '../inc/tpl/tpl.Training.edit.delete.php'; ?>
	</div>

	<div id="edit-gps" class="change" style="display:none;">
		<?php include '../inc/tpl/tpl.Training.edit.gps.php'; ?>
	</div>

	<div id="edit-general" class="change">
		<?php include '../inc/tpl/tpl.Training.edit.general.php'; ?>
	</div>

	<div id="edit-train" class="change" style="display:none;">
		<?php include '../inc/tpl/tpl.Training.edit.distance.php'; ?>
	</div>

	<div id="edit-out" class="change" style="display:none;">
		<?php include '../inc/tpl/tpl.Training.edit.outside.php'; ?>
	</div>
</div>

<br />

<input type="submit" value="Bearbeiten" />

</form>

<script type="text/javascript">
//<![CDATA[
<?php include '../lib/jQuery.form.include.php'; ?>
//]]>
</script>