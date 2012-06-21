<?php
$Links = array();
$Links[] = array('tag' => Ajax::window('<a href="'.Config::$CONFIG_URL.'?plugins" title="Konfiguration: alle Plugins">zur &Uuml;bersicht</a>'));

echo Ajax::toolbarNavigation($Links, 'right');
?>

<h1>Plugin: <?php echo $name; ?></h1>

<small class="right">
	<?php echo $activationLink; ?>
</small><br />

<form action="<?php echo self::$CONFIG_URL.'?id='.$this->id; ?>" class="ajax" id="pluginconfig" method="post">
	<fieldset>
		<legend>Beschreibung</legend>
		<div class="w100">
			<?php echo $this->displayLongDescription(); ?>
		</div>
	</fieldset>


		<?php if ($this->active == self::$ACTIVE_NOT): ?>
			<p class="warning">
				Das Plugin ist derzeit deaktiviert.
			</p>
		<?php endif; ?>

	<fieldset>
		<legend>Konfiguration</legend>
		<?php if (count($this->config) == 0): ?>
			<p class="info">
				Es sind <em>keine</em> Optionen vorhanden
			</p>
		<?php else: ?>

		<?php foreach ($this->config as $name => $config_var): ?>
			<div class="w100">
				<label for="conf_<?php echo $name; ?>">
					<?php echo $config_var['description']; ?>
				</label>
				<?php echo $this->getInputFor($name, $config_var); ?>
			</div>
		<?php endforeach; ?>

		<p class="text">
			<input type="hidden" name="edit" value="true" />
			<input type="submit" value="Bearbeiten" />
		</p>
<?php endif; ?>
	</fieldset>

</form>