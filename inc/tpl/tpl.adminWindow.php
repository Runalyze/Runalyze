<?php if ($AdminIsLoggedIn): ?>
	<p class="text">
		<strong>Willkommen im Administrationsbereich!</strong>
	</p>

	<fieldset>
		<legend>Nutzer</legend>

	<?php if (empty($AllUser)): ?>
		<p class="warning">Es sind keine Benutzer registriert.</p>
	<?php else: ?>
		<table class="small fullWidth" id="userTable">
			<thead>
				<tr>
					<th>#id</th>
					<th>User</th>
					<th>Name</th>
					<th>E-Mail</th>
					<th class="{sorter: 'x'}">Anz.</th>
					<th class="{sorter: 'distance'}">km</th>
					<th class="{sorter: 'germandate'}">seit</th>
					<th class="{sorter: 'germandate'}">zuletzt</th>
					<th>Funktionen</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($AllUser as $i => $User): ?>
				<tr class="<?php echo HTML::trClass($i); ?>">
					<td class="small r"><?php echo $User['id']; ?></td>
					<td><?php echo $User['username']; ?></td>
					<td><?php echo $User['name']; ?></td>
					<td class="small"><?php echo $User['mail']; ?></td>
					<td class="small r"><?php echo $User['num']; ?>x</td>
					<td class="small r"><?php echo Running::Km($User['km']); ?></td>
					<td class="small c"><?php echo date("d.m.Y", $User['registerdate']); ?></td>
					<td class="small c"><?php echo date("d.m.Y", $User['lastaction']); ?></td>
					<td>User aktivieren Neues Passwort zusenden</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<div class="small">
		<?php Ajax::createTablesorterWithPagerFor('#userTable'); ?>
		</div>
	<?php endif; ?>
	</fieldset>

	<?php
	$Fieldset = new FormularFieldset('Serverdaten');
	$Fieldset->addInfo('Derzeit l&auml;uft PHP '.PHP_VERSION);
	$Fieldset->addInfo('Es l&auml;uft MySQL '.@mysql_get_server_info());
	$Fieldset->addInfo('Zeit-Limit: '.ini_get('max_execution_time'));
	$Fieldset->addInfo('Memory-Limit: '.ini_get('memory_limit'));
	$Fieldset->addInfo('Upload-Limit: '.ini_get('upload_max_filesize'));
	$Fieldset->display();
	?>
<?php else: ?>
<div class="w50" id="loginWindow">
	<form action="" method="post">
		<fieldset>
			<legend>Administration</legend>
			<div class="w100 clear">
				<label for="password">Passwort</label>
				<input id="password" name="password" class="middleSize withUnit unitPass" type="password" />
			</div>

			<p class="text">&nbsp;</p>

			<div class="c">
				<input type="submit" value="Login" name="submit" />
			</div>
		</fieldset>
	</form>
</div>
<?php endif; ?>