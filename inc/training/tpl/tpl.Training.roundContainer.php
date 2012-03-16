<div id="trainingRounds" class="dataBox">
	<strong class="small">Rundenzeiten:&nbsp;</strong>
	<small class="right margin-5"><?php echo $RoundLinks; ?></small>

	<?php if (empty($RoundTypes)): ?>
	<small><em>Keine Daten vorhanden.</em></small>
	<?php else: ?>
		<?php foreach ($RoundTypes as $i => $RoundType): ?>
		<div id="<?php echo $RoundType['id']; ?>" class="change" <?php if ($i > 0) echo 'style="display:none;"'; ?>>
			<?php eval($RoundType['eval']); ?>
		</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>