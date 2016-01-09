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

		$LanguageField = new FormularSelectBox('language', __('Language'), $Data['language']);

		foreach (Language::availableLanguages() as $klang => $lang) {
			$LanguageField->addOption($klang, $lang[0]);
		}


		$SinceField = new FormularInput('name', __('Registered since'), date('d.m.Y H:i', $Data['registerdate']));
		$SinceField->setDisabled();

		$LastLoginField = new FormularInput('name', __('Last Login'), date('d.m.Y H:i', $Data['lastlogin']));
		$LastLoginField->setDisabled();

		$Account = new FormularFieldset( __('Your account') );
		$Account->addField($UsernameField);
		$Account->addField($MailField);
		$Account->addField($NameField);
		$Account->addField($LanguageField);
		$Account->addField($SinceField);
		$Account->addField($LastLoginField);

		$AllowMailsField = new FormularSelectBox('allow_mails', __('Email me'), $Data['allow_mails']);
		$AllowMailsField->addOption('1', __('Yes'));
		$AllowMailsField->addOption('0', __('No'));

		$Notifications = new FormularFieldset( __('Notifications') );
		$Notifications->addInfo(__('At irregular intervals we are sending mails to you. We will never send you spam or advertisement.'));
		$Notifications->addField($AllowMailsField);

		$Password =  new FormularFieldset(__('Change your password'));

		if (empty($_POST['old_pw']) && empty($_POST['new_pw']) && empty($_POST['new_pw_repeat'])) {
			$Password->setCollapsed();
		} else {
			// Don't show passwords as 'value="..."'
			$_POST['old_pw'] = '';
			$_POST['new_pw'] = '';
			$_POST['new_pw_repeat'] = '';
		}

		$Password->addField( new FormularInputPassword('old_pw', __('Old password')) );
		$Password->addField( new FormularInputPassword('new_pw', __('New password')) );
		$Password->addField( new FormularInputPassword('new_pw_repeat', __('Repeat new password')) );

		$Backup = new FormularFieldset( __('Backup your data') );
		$Backup->setCollapsed();

		$Factory = new PluginFactory();

		if ($Factory->isInstalled('RunalyzePluginTool_DbBackup')) {
			$Plugin = $Factory->newInstance('RunalyzePluginTool_DbBackup');
			$Backup->addInfo( __('Please use the plugin').' \'<strong>'.$Plugin->getWindowLink().'</strong>\'.' );
		} else {
			$Backup->addInfo( __('The backup of all your data is not manually possible yet.<br>'.
								'In important individual cases write us an e-mail to mail@runalyze.de and and we will take care of it right away!') );
		}

		$DeleteLink = Ajax::window('<a href="call/window.delete.php"><strong>'.__('Delete your account').' &raquo;</strong></a>').
						'<br><br>'.
						__('You\'ll receive an email with a link to confirm the deletion.<br>'.
						'The deletion is permanent and cannot be reversed. '.
						'Therefore, you should backup your data.');

		$Delete = new FormularFieldset(__('Delete your account'));
		$Delete->setCollapsed();
		$Delete->addWarning($DeleteLink);

		$this->Formular->addFieldset($Account);
		$this->Formular->addFieldset($Notifications);
		$this->Formular->addFieldset($Password);
		$this->Formular->addFieldset($Backup);
		$this->Formular->addFieldset($Delete);
		$this->Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );
	}

	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		if ($_POST['name'] != SessionAccountHandler::getName()) {
			DB::getInstance()->update('account', SessionAccountHandler::getId(), 'name', $_POST['name']);
		}
                
		if ($_POST['allow_mails'] != SessionAccountHandler::getAllowMails()) {
			DB::getInstance()->update('account', SessionAccountHandler::getId(), 'allow_mails', $_POST['allow_mails']);
		}
                
		if ($_POST['language'] != SessionAccountHandler::getLanguage()) {
			DB::getInstance()->update('account', SessionAccountHandler::getId(), 'language', $_POST['language']);
			Language::setLanguage($_POST['language']);

			echo Ajax::wrapJS('document.cookie = "lang=" + encodeURIComponent("'.addslashes($_POST['language']).'");');
			Ajax::setReloadFlag( Ajax::$RELOAD_PAGE );
		}

		if ($_POST['new_pw'] != '') {
			$this->tryToChangePassword();
		}
	}

	/**
	 * Try to change password
	 */
	private function tryToChangePassword() {
		if ($_POST['new_pw'] == $_POST['new_pw_repeat']) {
			$Account = DB::getInstance()->query('SELECT `password`, `salt` FROM `'.PREFIX.'account`'.' WHERE id = '.SessionAccountHandler::getId())->fetch();   

			if (AccountHandler::comparePasswords($_POST['old_pw'], $Account['password'], $Account['salt'])) {
				if (strlen($_POST['new_pw']) < AccountHandler::$PASS_MIN_LENGTH) {
					ConfigTabs::addMessage( HTML::error(sprintf( __('The password has to contain at least %s characters.'), AccountHandler::$PASS_MIN_LENGTH)) );
				} else {
					AccountHandler::setNewPassword(SessionAccountHandler::getUsername(), $_POST['new_pw']);
					ConfigTabs::addMessage( HTML::okay (__('Your password has been changed.')) );
				}
			} else {
				ConfigTabs::addMessage( HTML::error (__('You current password is wrong.')) );
			}
		} else {
			ConfigTabs::addMessage( HTML::error(__('The passwords have to be the same.')) );
		}
	}
}