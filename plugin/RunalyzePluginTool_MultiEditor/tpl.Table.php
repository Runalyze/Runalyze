<p<?php if (isset($_GET['ids'])) echo ' class="hide"'; ?>>
	<?php echo DataBrowser::getSearchLink('Suchergebnisse'); ?>
	k&ouml;nnen als Auswahl an den MultiEditor gesendet werden.<br />
	Standardm&auml;&szlig;ig werden die letzten Trainings zum Bearbeiten angezeigt.
</p>

<form class="ajax" action="<?php echo Plugin::$DISPLAY_URL.'?id='.$this->id; ?>" id="training" onsubmit="return false;" method="post">

<input type="hidden" name="type" value="training" />
<input type="hidden" name="ids" value="<?php echo implode(',', $this->IDs); ?>" />

<div style="width:100%;max-height:400px;overflow:scroll;">
<table>
	<thead>
		<tr>
			<th></th>
			<th>Datum</th>
			<?php foreach ($this->Keys as $key => $Data): ?>
			<?php if ($this->config[$key]['var']): ?>
			<th><?php echo $Data['name']; ?></th>
			<?php endif; ?>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
	<?php if (empty($this->Trainings)): ?>
		<tr><td colspan="2"><em>Keine Trainings zum Bearbeiten ausgew&auml;hlt.</em></td></tr>
	<?php else: ?>
	<?php foreach ($this->Trainings as $i => $Training): ?>
		<?php HTML::setMultiIndex($Training->get('id')); ?>
		<?php $Training->overwritePostArray(); ?>
		<tr class="<?php echo HTML::trClass($i); ?>">
			<td><?php echo Icon::get(Icon::$DELETE_GRAY, '', '$(this).parent().parent().remove();', 'Zeile entfernen'); ?></td>
			<td nowrap="nowrap">
				<?php echo HTML::hiddenInput('sportid_old'); ?>
				<?php echo HTML::hiddenInput('s_old'); ?>
				<?php echo HTML::hiddenInput('dist_old'); ?>
				<?php echo HTML::hiddenInput('shoeid_old'); ?>

				<span style="display:none;">
				<?php foreach ($this->Keys as $key => $Data): ?>
				<?php if (!$this->config[$key]['var']) eval($Data['eval']); ?>
				<?php endforeach; ?>
				</span>

				<?php echo HTML::simpleInputField('datum', 10).HTML::simpleInputField('zeit', 4); ?>
			</td>

			<?php foreach ($this->Keys as $key => $Data): ?>
			<?php if ($this->config[$key]['var']): ?>
			<td><?php eval($Data['eval']); ?></td>
			<?php endif; ?>
			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
	<?php HTML::setMultiIndex(false); ?>
	<?php endif; ?>
	</tbody>
</table>
</div>

	<div class="c">
		<input type="submit" value="Bearbeiten" />
	</div>

<?php if (!empty($this->Errors)) echo HTML::error(implode('<br />', $this->Errors)); ?>

<?php if (!empty($this->Infos)) echo HTML::info(implode('<br />', $this->Infos)); ?>

</form>