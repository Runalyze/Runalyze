<?php
class Request {
	/**
	 * Get requested URI
	 * @return string
	 */
	static public function Uri() {
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Was the request an AJAX-request?
	 * @return boolean
	 */
	static public function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	/**
	 * Get ID send as post or get
	 * @return mixed
	 */
	static public function sendId() {
		if (isset($_GET['id']))
			return $_GET['id'];
		if (isset($_POST['id']))
			return $_POST['id'];

		return false;
	}
}