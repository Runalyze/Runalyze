	<div class="panel clear">
		<div class="panel-heading"><h1><?php _e('Debug console'); ?></h1></div>
		<div class="panel-content"><?php echo \Runalyze\Error::getInstance()->display(); ?></div>
	</div>