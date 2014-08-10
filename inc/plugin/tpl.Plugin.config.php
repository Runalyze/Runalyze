<?php
$activationLink = $this->isInActive()
	? $this->getConfigLink( __('Activate plugin'), '&active='.Plugin::ACTIVE)
	: $this->getConfigLink( __('Deactivate plugin'), '&active='.Plugin::ACTIVE_NOT);

$name = ($this instanceof PluginTool)
	? $this->getWindowLink()
	: $this->name();

$Links = array();
$Links[] = array('tag' => Ajax::window('<a href="'.ConfigTabPlugins::getExternalUrl().'">'.__('back to overview').'</a>'));

echo '<div class="panel-heading">';
echo '<div class="panel-menu">';
echo Ajax::toolbarNavigation($Links);
echo '</div>';
echo '<h1>'.__('Plugin configuration').': '.$name.'</h1>';
echo '</div>';

if (!empty($_POST)) {
	Ajax::setPluginIDtoReload( $this->id() );
	Ajax::setReloadFlag( Ajax::$RELOAD_PLUGINS );
	echo Ajax::getReloadCommand();
}
?>
<div class="panel-content">
	<form action="<?php echo self::$CONFIG_URL.'?id='.$this->id(); ?>" class="ajax no-automatic-reload" id="pluginconfig" method="post">
		<fieldset>
			<legend><?php _e('Description'); ?></legend>
			<div class="w100">
				<?php $this->displayLongDescription(); ?>
			</div>

			<?php if ($this->isInActive()): ?>
			<p class="warning">
				<?php _e('The plugin is deactivated.'); ?>
			</p>
			<?php endif; ?>
		</fieldset>

		<fieldset>
			<legend><?php _e('Configuration'); ?></legend>
			<?php if (count($this->config) == 0): ?>
				<p class="info">
					<?php _e('There are no settings.'); ?>
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

			<p clas="test">
				&nbsp;
			</p>

			<p class="text">
				<input type="hidden" name="edit" value="true">
				<input type="submit" value="<?php _e('Edit'); ?>">
			</p>
	<?php endif; ?>
		</fieldset>

		<fieldset>
			<legend><?php _e('Activation'); ?></legend>
			<p class="warning">
				<?php echo $activationLink; ?>
			</p>
		</fieldset>

	</form>

	<?php if ($this->type() == PluginType::Tool && $this->isActive()): ?>
		<ul class="blocklist">
			<li><?php echo $this->getWindowLink(Icon::$CALCULATOR.' '.__('Open tool'), true); ?></li>
		</ul>
	<?php endif; ?>
</div>