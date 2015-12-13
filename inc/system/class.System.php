<?php
/**
 * This file contains class::System
 * @package Runalyze\System
 */
/**
 * System
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
use Runalyze\Error;

class System {
	/**
	 * Get code to include all local JS-files
	 * @return string 
	 */
	public static function getCodeForLocalJSFiles() {
		if (self::isAtLocalhost()) {
			return '<script src="build/scripts.js?v='.RUNALYZE_VERSION.'"></script>';
		}

		return '<script src="build/scripts.min.js?v='.RUNALYZE_VERSION.'"></script>';
	}

	/**
	 * Get code to include all external JS-files
	 * @return string 
	 */
	public static function getCodeForExternalJSFiles() {
		return '';
	}

	/**
	 * Get code to include all CSS-files
	 * @return string 
	 */
	public static function getCodeForAllCSSFiles() {
		return '<link rel="stylesheet" href="lib/less/runalyze-style.css?v='.RUNALYZE_VERSION.'">';
	}

	/**
	 * Send an email via smtp
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @return boolean 
	 */
	public static function sendMail($to, $subject, $message) {
		$sender = MAIL_SENDER == '' ? 'mail@runalyze.de' : MAIL_SENDER;

		try {
			$message = Swift_Message::newInstance()
					->setSubject($subject)
					->setBody($message, 'text/html')
					->setFrom(array($sender => MAIL_NAME))
					->setTo($to);
			$transport = Swift_SmtpTransport::newInstance(SMTP_HOST, SMTP_PORT, SMTP_SECURITY)
				->setUsername(SMTP_USERNAME)
				->setPassword(SMTP_PASSWORD);
			$mailer = Swift_Mailer::newInstance($transport);
			return $mailer->send($message);
		} catch (Exception $e) {
			Error::getInstance()->addError('Mail could not be sent: '.$e->getMessage());
			return false;
		}
	}

	/**
	 * Set memory- and time-limit as high as possible 
	 */
	public static function setMaximalLimits() {
		@ini_set('memory_limit', '-1');

		if (!ini_get('safe_mode')) {
			set_time_limit(0);
		}
	}

	/**
	 * Get domain where Runalyze is running
	 * @return string
	 */
	public static function getDomain() {
		if (!isset($_SERVER['HTTP_HOST'])) {
			return '';
		}

		return Request::getProtocol().'://'.$_SERVER['HTTP_HOST'];
	}

	/**
	 * Get full domain
	 * @param boolean $onlyToRunalyze
	 * @return string
	 */
	public static function getFullDomain($onlyToRunalyze = true) {
		$path = self::getDomain().substr($_SERVER['SCRIPT_NAME'], 0, strripos($_SERVER['SCRIPT_NAME'], "/"))."/";

		if ($onlyToRunalyze) {
			$path = str_replace(array('call/', 'inc/', 'tpl/'), array('', '', ''), $path);
		}

		return $path;
	}

	/**
	 * Is this script running on localhost?
	 * @return boolean
	 */
	public static function isAtLocalhost() {
		if (!isset($_SERVER['SERVER_NAME'])) {
			return false;
		}

		return $_SERVER['SERVER_NAME'] == 'localhost';
	}

	/**
	 * Clear complete cache 
	 */
	public static function clearCache() {
		Cache::clean();
	}
}
