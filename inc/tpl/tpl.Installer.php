	<p class="text c">
<?php
$Steps = array(
	1 => __('Start'),
	2 => __('Configuration'),
	3 => __('Database'),
	4 => __('Ready')
);

foreach ($Steps as $i => $Name) {
	$opacity = ($i == $this->currentStep) ? '1' : '0.5';

	echo '<strong style="opacity:'.$opacity.';">'.$Name.'</strong>';

	if ($i != 4)
		echo ' &nbsp;&nbsp;&raquo;&nbsp;&nbsp; ';
}
?>
	</p>


<?php if ($this->currentStep == self::ALREADY_INSTALLED): ?>

	<p class="text-headline">
		<?php _e('Runalyze is already installed.'); ?>
	</p>

	<p class="text">
		<?php _e('You cannot reinstall Runalyze. If something does not work, please do not hesitate to contact us.'); ?>
	</p>

	<p class="text">
		<a class="button" href="index.php" title="Runalyze"><?php _e('Start Runalyze'); ?></a>
	</p>

	<p class="warning">
		<?php _e('If you want to reinstall Runalyze please delete the <em>data/config.php</em>-file in your main directory of this installation.'); ?>
	</p>

<?php elseif ($this->currentStep == self::START): ?>

<form action="install.php" method="post">
	<p class="text">
		<strong><?php _e('Welcome!'); ?></strong>
	</p>

	<p class="text">
		<?php _e('Let us first check if your server fulfills the requirements.'); ?>
	</p>

	<?php if (!$this->phpVersionIsOkay()): ?>
	<p class="error">
		<?php printf( __('PHP %s is required, but PHP %s is running. Please update your PHP version.'), self::REQUIRED_PHP_VERSION, PHP_VERSION); ?>
	</p>
	<?php else: ?>
	<p class="okay">
		<?php printf( __('Currently PHP %s is running.'), PHP_VERSION); ?>
	</p>
	<?php endif; ?>

	<p class="text">&nbsp;</p>

	<p class="text">
		<?php _e('Importing large files (e.g. a SportTracks logbook) may take some time. Please have a look at your server limitations:'); ?>
	</p>

	<p class="info"><?php _e('Time limit'); ?>: <?php echo ini_get('max_execution_time'); ?>s</p>
	<p class="info"><?php _e('Memory limit'); ?>: <?php echo ini_get('memory_limit'); ?></p>
	<p class="info"><?php _e('Upload limit'); ?>: <?php echo ini_get('upload_max_filesize'); ?></p>

	<p class="text">&nbsp;</p>

	<p class="text">
			<input type="hidden" name="step" value="2">

			<input type="submit" value="<?php _e('Start installation'); ?>">
	</p>
</form>

<?php elseif ($this->currentStep == self::SETUP_CONFIG): ?>

<form action="install.php" method="post">
	<p class="text">
		<strong><?php _e('Settings for Runalyze'); ?></strong>
	</p>

	<p class="text">
		<?php _e('Runalyze does need a MySQL database.'); ?>
	</p>

	<?php if ($this->connectionIsIncorrect): ?>
		<p class="error">
			<?php _e('The connection settings are incorrect. We are not able to connect to the database.'); ?>
		</p>
	<?php else: ?>
		<p class="okay">
			<?php _e('A connection could be established.'); ?>
		</p>

		<?php if ($this->mysqlVersionIsOkay()): ?>
		<p class="okay">
			<?php printf( __('Currently MySQL %s is running.'), $this->getMysqlVersion()); ?>
		</p>
		<?php elseif (!$this->cantWriteConfig): ?>
		<p class="error">
			<?php printf( __('MySQL %s is required, but MySQL %s is running. Please update your MySQL version.'), self::REQUIRED_MYSQL_VERSION, $this->getMysqlVersion()); ?>
		</p>
		<?php endif; ?>
	<?php endif; ?>

	<p class="text">
		<label>
			<strong><?php _e('Host server'); ?></strong>
			<input type="text" name="host" value="<?php echo (isset($_POST['host']) ? $_POST['host'] : 'localhost'); ?>" <?php if ($this->readyForNextStep) echo 'readonly'; ?>>
		</label>
	</p>
	<p class="text">
		<label>
			<strong><?php _e('Database'); ?></strong>
			<input type="text" name="database" value="<?php echo (isset($_POST['database']) ? $_POST['database'] : 'runalyze'); ?>" <?php if ($this->readyForNextStep) echo 'readonly'; ?>>
		</label>
	</p>
	<p class="text">
		<label>
			<strong><?php _e('Port'); ?></strong>
			<input type="text" name="port" value="<?php echo (isset($_POST['port']) ? $_POST['port'] : '3306'); ?>" <?php if ($this->readyForNextStep) echo 'readonly'; ?>>
		</label>
	</p>
	<p class="text">
		<label>
			<strong><?php _e('User'); ?></strong>
			<input type="text" name="username" value="<?php echo (isset($_POST['username']) ? $_POST['username'] : 'root'); ?>" <?php if ($this->readyForNextStep) echo 'readonly'; ?>>
		</label>
	</p>
	<p class="text">
		<label>
			<strong><?php _e('Password'); ?></strong>
			<input type="password" name="password" value="<?php echo (isset($_POST['password']) ? $_POST['password'] : ''); ?>" <?php if ($this->readyForNextStep) echo 'readonly'; ?>>
		</label>
	</p>

	<p class="text">
		<?php _e('You can use a specific database prefix if you want to run multiple installations of Runalyze.'); ?>
	</p>

	<?php if ($this->prefixIsAlreadyUsed): ?>
	<p class="error">
		<?php _e('This prefix is already being used.'); ?>
	</p>
	<?php elseif (!$this->connectionIsIncorrect): ?>
	<p class="okay">
		<?php _e('This prefix is free.'); ?>
	</p>
	<?php endif; ?>

	<p class="text">
		<label>
			<strong><?php _e('Prefix'); ?></strong>
			<input type="text" name="prefix" value="<?php echo (isset($_POST['prefix']) ? $_POST['prefix'] : 'runalyze_'); ?>" <?php if ($this->readyForNextStep) echo 'readonly'; ?>>
		</label>
	</p>

	<p class="text">
		<?php _e('You can activate a debug toolbar to see specific information if problems occur.'); ?>
	</p>

	<p class="text">
		<label>
			<strong><?php _e('Debug mode'); ?></strong>
			<input type="checkbox" name="debug" <?php if (isset($_POST['debug']) && $_POST['debug']) echo 'checked' ?>>
		</label>
	</p>

	<p class="text">
		<label>
			<strong><?php _e('Garmin API key'); ?>*</strong>
			<input type="text" name="garminkey" value="<?php echo (isset($_POST['garminkey']) ? $_POST['garminkey'] : ''); ?>">
			<?php if ($_SERVER['SERVER_NAME'] == 'localhost'): ?>
				<small>(<?php _e('not necessary for localhost'); ?>)</small>
			<?php else: ?>
				<small>
					(<?php _e('necessary for'); ?> <em><?php echo ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']; ?></em>,
					<?php _e('see'); ?> <a href="http://developer.garmin.com/web-device/garmin-communicator-plugin/get-your-site-key/">developer.garmin.com</a>)
				</small>
			<?php endif; ?>
		</label>
	</p>

	<p class="text">
		<small>
			* <?php _e('A Garmin API key is necessary for using Garmin Communicator (enables direct imports from any Garmin Forerunner).'); ?>
		</small>
	</p>

	<?php if ($this->cantWriteConfig): ?>
	<p class="error">
		<?php _e('The configuration file cannot be written.'); ?>
	</p>

	<?php if (empty($this->writeConfigFileString)): ?>
	<p class="error">
		<?php printf( __('Please copy <strong>%s</strong> to the directory <strong>%s</strong> and insert your data.'), '/runalyze/inc/install/config.php', '/runalyze/'); ?>
		<?php _e('You have to change the following variables:'); ?>
		<em>'{config::host}'</em> &raquo; <em>'<?php echo $_POST['host']; ?>'</em><br>
		<em>'{config::database}'</em> &raquo; <em>'<?php echo $_POST['database']; ?>'</em><br>
		<em>'{config::username}'</em> &raquo; <em>'<?php echo $_POST['username']; ?>'</em><br>
		<em>'{config::password}'</em> &raquo; <em>'<?php echo $_POST['password']; ?>'</em><br>
		<em>'{config::prefix}'</em> &raquo; <em>'<?php echo $_POST['prefix']; ?>'</em><br>
		<em>{config::debug}</em> &raquo; <em><?php echo isset($_POST['debug']) ? 'true' : 'false'; ?></em><br>
		<em>{config::garminkey}</em> &raquo; <em><?php echo $_POST['garminkey']; ?><br>
	</p>
	<?php else: ?>
	<p class="error">
		<?php printf( __('Please save the following code as <strong>%s</strong>:'), '/runalyze/data/config.php'); ?>
	</p>
	<textarea class="code"><?php echo htmlspecialchars($this->writeConfigFileString); ?></textarea>
	<?php endif; ?>
	<?php endif; ?>

	<p class="text">
		<?php if ($this->readyForNextStep || $this->cantWriteConfig): ?>
			<input type="hidden" name="write_config" value="true">
		<?php endif; ?>
		<input type="hidden" name="step" value="2">

		<input type="submit" value="<?php echo $this->cantWriteConfig ? __('Written! Continue ...') : ( $this->readyForNextStep ? __('Write configuration file') : __('Check settings') ); ?>">
	</p>
</form>

<?php elseif ($this->currentStep == self::SETUP_DATABASE): ?>

<form action="install.php" method="post">
	<p class="text">
		<?php _e('The configuration file has been written.'); ?>
	</p>

	<p class="text">
		<?php _e('Next step: setup database'); ?>
	</p>

	<textarea class="code"><?php echo $this->getSqlContentForFrontend('inc/install/structure.sql'); ?></textarea>

	<?php if ($this->cantSetupDatabase): ?>
	<p class="error">
		<?php _e('There are some problems with filling the database. Please insert the above SQL-statements by hand (e.g. via PhpMyAdmin).'); ?>
	<?php endif; ?>

	<p class="text">
		<input type="hidden" name="step" value="3">

		<input type="submit" value="<?php _e('Setup database'); ?>">
	</p>
</form>

<?php elseif ($this->currentStep == self::READY): ?>

	<p class="text">
		<strong><?php _e('Ready! Congratulations!'); ?></strong>
	</p>

	<p class="text">
		<?php _e('Runalyze has been successfully installed. Have fun while using it!'); ?>
	</p>

	<p class="text">
		<a class="button" href="index.php" title="Runalyze"><?php _e('Start Runalyze'); ?></a>
	</p>

<?php
$CHMOD_FOLDERS = array();
include PATH.'system/define.chmod.php';

foreach ($CHMOD_FOLDERS as $folder)
	@chmod(PATH.'../'.$folder, 0777);

clearstatcache();

foreach ($CHMOD_FOLDERS as $folder) {
	$realfolder = PATH.'../'.$folder;

	if (!is_writable($realfolder))
		printf( '<p class="error">'.__('The directory <strong>%s</strong> is not writable.').' <em>(chmod = %s)</em></p>', $folder, substr(decoct(fileperms($realfolder)),1));
}
?>

<?php endif; ?>

	<noscript>
		<p class="error" id="JSerror">
			<?php _e('Please activate JavaScript, Runalyze will not work without.'); ?>
		</p>
	</noscript>

	<p class="text">
		&nbsp;
	</p>
