<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>?id=<?php echo $id; ?>" id="training" onsubmit="return false;" method="post">


<div id="edit-div">
	<div id="delete" class="change" style="display:none;">
		<?php include '../inc/training/tpl/tpl.Training.edit.delete.php'; ?>
	</div>

	<div id="edit-gps" class="change" style="display:none;">
		<?php include '../inc/training/tpl/tpl.Training.edit.gps.php'; ?>
	</div>

	<div id="edit-general" class="change">
		<?php include '../inc/training/tpl/tpl.Training.edit.general.php'; ?>
	</div>

	<div id="edit-train" class="change" style="display:none;">
		<?php include '../inc/training/tpl/tpl.Training.edit.distance.php'; ?>
	</div>

	<div id="edit-out" class="change" style="display:none;">
		<?php include '../inc/training/tpl/tpl.Training.edit.outside.php'; ?>
	</div>
</div>

<br />

<input type="submit" value="Speichern" />

</form>