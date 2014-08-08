<?php
/**
 * This file contains class::ConfigTabAccount
 * @package Runalyze\System\Config
 */
/**
 * ConfigTabAccount
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigTabAccount extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_account';
		$this->title = __('Account');
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Data = AccountHandler::getDataForId(SessionAccountHandler::getId());

		FormularInput::setStandardSize( FormularInput::$SIZE_MIDDLE );

		$UsernameField = new FormularInput('name', __('Username'), $Data['username']);
		$UsernameField->setDisabled();

		$MailField = new FormularInput('name', __('Email address'), $Data['mail']);
		$MailField->setDisabled();

		$NameField = new FormularInput('name', __('Name'), $Data['name']);

		$SinceField = new FormularInput('name', __('Registered since'), date('d.m.Y H:i', $Data['registerdate']));
		$SinceField->setDisabled();

		$LastLoginField = new FormularInput('name', __('Last Login'), date('d.m.Y H:i', $Data['lastlogin']));
		$LastLoginField->setDisabled();

		$Account = new FormularFieldset( __('Your account') );
		$Account->addField($UsernameField);
		$Account->addField($MailField);
		$Account->addField($NameField);
		$Account->addField($SinceField);
		$Account->addField($LastLoginField);

		$Backup = new FormularFieldset( __('Backup your data') );
		$Backup->setCollapsed();

		$Factory = new PluginFactory();

		if ($Factory->isInstalled('RunalyzePluginTool_DbBackup')) {
			$Plugin = $Factory->newInstance('RunalyzePluginTool_DbBackup');
			$Backup->addInfo( __('Please use the plugin').' \'<strong>'.$Plugin->getWindowLink().'</strong>\'.' );
		} else {
			$Backup->addInfo( __('The back up of all your data is not manually possible yet.<br>'.
								'In important individual cases write us an e-mail to mail@runalyze.de and and we will take care of it right away!') );
		}

		$DeleteLink = Ajax::window('<a href="call/window.delete.php"><strong>'.__('Delete your account').' &raquo;</strong></a>').
						'<br><br>'.
						__('You\'ll receive an email with a link to confirm the deletion.<br>'.
						'The deletion is permanently and can\'t be reversed. '.
						'Therefore, you should backup your data.');

		$Delete = new FormularFieldset(__('Delete your account'));
		$Delete->setCollapsed();
		$Delete->addWarning($DeleteLink);

		$this->Formular->addFieldset($Account);
		$this->Formular->addFieldset($Backup);
		$this->Formular->addFieldset($Delete);
		$this->Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );
	}

	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		if ($_POST['name'] != SessionAccountHandler::getName())
			DB::getInstance()->update('account', SessionAccountHandler::getId(), 'name', $_POST['name'], false);
	}
}