<?php
// TODO
// Passwort "senden"?
/*
$Configuration = new FormularFieldset('Einstellungen');
$Configuration->addField( new FormularCheckbox('can_register', 'Benutzer k&ouml;nnen sich registrieren') );
$Configuration->setCollapsed();
$Configuration->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50_AS_W100 );

$ConfigFormular = new Formular();
$ConfigFormular->setId('admin-window');
$ConfigFormular->addFieldset($Configuration);
$ConfigFormular->addSubmitButton('Speichern');
$ConfigFormular->display();
*/

$UserList = new FormularFieldset('Benutzerliste');

if (empty($AllUser)) {
	$UserList->addWarning('Es ist noch niemand registriert.');
} else {
	$Code = '
	<table class="small fullWidth" id="userTable">
		<thead>
			<tr>
				<th>ID</th>
				<th>User</th>
				<th>Name</th>
				<th>E-Mail</th>
				<th class="{sorter: \'x\'}">Anz.</th>
				<th class="{sorter: \'distance\'}">km</th>
				<th class="{sorter: \'germandate\'}">seit</th>
				<th class="{sorter: \'germandate\'}">zuletzt</th>
				<!--<th class="{sorter: false}">Funktionen</th>-->
			</tr>
		</thead>
		<tbody>';

	foreach ($AllUser as $i => $User) {
		$Code .= '
			<tr class="'.HTML::trClass($i).'">
				<td class="small r">'.$User['id'].'</td>
				<td>'.$User['username'].'</td>
				<td>'.$User['name'].'</td>
				<td class="small">'.$User['mail'].'</td>
				<td class="small r">'.$User['num'].'x</td>
				<td class="small r">'.Running::Km($User['km']).'</td>
				<td class="small c">'.date("d.m.Y", $User['registerdate']).'</td>
				<td class="small c">'.date("d.m.Y", $User['lastaction']).'</td>
				<!--<td>User aktivieren Neues Passwort zusenden</td>-->
			</tr>';
	}

	$Code .= '
		</tbody>
	</table>

	<div class="small">
		'.Ajax::getTablesorterWithPagerFor('#userTable').'
	</div>';

	$UserList->addBlock($Code);
}


$ServerInformation = new FormularFieldset('Serverdaten');
$ServerInformation->addSmallInfo('Derzeit l&auml;uft PHP '.PHP_VERSION);
$ServerInformation->addSmallInfo('Es l&auml;uft MySQL '.@mysql_get_server_info());
$ServerInformation->addSmallInfo('Zeit-Limit: '.ini_get('max_execution_time'));
$ServerInformation->addSmallInfo('Memory-Limit: '.ini_get('memory_limit'));
$ServerInformation->addSmallInfo('Upload-Limit: '.ini_get('upload_max_filesize'));
$ServerInformation->setCollapsed();


$Formular = new Formular();
$Formular->setId('admin-window');
$Formular->addFieldset($UserList);
$Formular->addFieldset($ServerInformation);
$Formular->display();
?>