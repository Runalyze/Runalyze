<?php
$Links = array();
$Links[] = array('tag' => Ajax::window('<a href="'.ConfigTabPlugins::getExternalUrl().'" title="Konfiguration: alle Plugins">zur &Uuml;bersicht</a>'));

echo Ajax::toolbarNavigation($Links, 'right');

if (!empty($_POST)) {
	Ajax::setPluginIDtoReload($this->id);
	Ajax::setReloadFlag( Ajax::$RELOAD_PLUGINS );
	echo Ajax::getReloadCommand();
}
?>

<h1>Plugin: <?php echo $name; ?></h1>

<form action="<?php echo self::$CONFIG_URL.'?id='.$this->id; ?>" class="ajax no-automatic-reload" id="pluginconfig" method="post">
	<fieldset>
		<legend>Beschreibung</legend>
		<div class="w100">
			<?php echo $this->displayLongDescription(); ?>
		</div>

		<?php if ($this->active == self::$ACTIVE_NOT): ?>
		<p class="warning">
			Das Plugin ist derzeit deaktiviert.
		</p>
		<?php endif; ?>
	</fieldset>

	<fieldset>
		<legend>Konfiguration</legend>
		<?php if (count($this->config) == 0): ?>
			<p class="info">
				Es sind keine Einstellungen m&ouml;glich.
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

	<fieldset>
		<legend>Aktivierung</legend>
		<p class="warning">
			<?php echo $activationLink; ?>
		</p>
	</fieldset>

</form>

<?php if ($this->type == Plugin::$TOOL): ?>
	<ul class="blocklist">
		<li><?php echo $this->getWindowLink('&raquo; zum Tool', true); ?></li>
	</ul>
<?php endif; ?>