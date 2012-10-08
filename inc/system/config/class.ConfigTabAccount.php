<?php
/**
 * Class: ConfigTabAccount
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ConfigTabAccount extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_account';
		$this->title = 'Account';
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Data = AccountHandler::getDataForId(SessionAccountHandler::getId());

		FormularInput::setStandardSize( FormularInput::$SIZE_MIDDLE );

		$UsernameField = new FormularInput('name', 'Username', $Data['username']);
		$UsernameField->setDisabled();

		$MailField = new FormularInput('name', 'E-Mail-Adresse', $Data['mail']);
		$MailField->setDisabled();

		$NameField = new FormularInput('name', 'Name', $Data['name']);

		$SinceField = new FormularInput('name', 'Registriert seit', date('d.m.Y H:i', $Data['registerdate']));
		$SinceField->setDisabled();

		$LastLoginField = new FormularInput('name', 'Letzte Anmeldung', date('d.m.Y H:i', $Data['lastlogin']));
		$LastLoginField->setDisabled();

		$Account = new FormularFieldset('Dein Account');
		$Account->addField($UsernameField);
		$Account->addField($MailField);
		$Account->addField($NameField);
		$Account->addField($SinceField);
		$Account->addField($LastLoginField);

		$Backup = new FormularFieldset('Daten sichern');
		$Backup->setCollapsed();
		$Backup->addInfo('Das Sichern aller Daten ist bisher nicht manuell m&ouml;glich.<br />
						In wichtigen Einzelf&auml;llen kannst du uns eine E-Mail an mail@runalyze.de schicken und wir k&uuml;mmern uns darum.');

		$DeleteLink  = Ajax::window('<a href="call/window.delete.php"><strong>Account unwiderruflich l&ouml;schen &raquo;</strong></a>');
		$DeleteLink .= '<br /><small>Nach dem L&ouml;schen erh&auml;lst du eine E-Mail mit dem Link zum L&ouml;schen deines Accounts.
						Das L&ouml;schen kann danach nicht r&uuml;ckg&auml;ngig gemacht werden.
						Du solltest daher deine Daten sichern, falls du doch noch einmal zur&uuml;ckkehren m&ouml;chtest.';

		$Delete = new FormularFieldset('Account l&ouml;schen');
		$Delete->setCollapsed();
		$Delete->addWarning($DeleteLink);

		$this->Formular->addFieldset($Account);
		$this->Formular->addFieldset($Backup);
		$this->Formular->addFieldset($Delete);
		$this->Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );
		$this->Formular->allowOnlyOneOpenedFieldset();
	}

	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		if ($_POST['name'] != SessionAccountHandler::getName())
			Mysql::getInstance()->update(PREFIX.'account', SessionAccountHandler::getId(), 'name', $_POST['name'], false);
	}
}