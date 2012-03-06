<?php
class Request {
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
?>
