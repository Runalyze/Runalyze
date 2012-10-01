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

		$Delete = new FormularFieldset('Account l&ouml;schen');
		$Delete->setCollapsed();
		$Delete->addInfo('Das L&ouml;schen des Accounts ist bisher nur manuell m&ouml;glich.<br />
						Bei einer lokalen Installation kannst du das selbst in der Datenbank vornehmen bzw. den Administrator darum bitten.
						In der Online-Version gen&uuml;gt eine E-Mail an mail@runalyze.de.');

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