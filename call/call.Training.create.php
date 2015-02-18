<?php
/**
 * File for displaying the formular for creating a new training.
 * Call:   call.Training.create.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(isset($_GET['json']));

System::setMaximalLimits();

if (class_exists('Normalizer')) {
	if (isset($_GET['file'])) {
		$_GET['file'] = Normalizer::normalize($_GET['file']);
	}

	if (isset($_GET['files'])) {
		$_GET['files'] = Normalizer::normalize($_GET['files']);
	}

	if (isset($_POST['forceAsFileName'])) {
		$_POST['forceAsFileName'] = Normalizer::normalize($_POST['forceAsFileName']);
	}

	if (isset($_FILES['qqfile']) && isset($_FILES['qqfile']['name'])) {
		$_FILES['qqfile']['name'] = Normalizer::normalize($_FILES['qqfile']['name']);
	}
}

$Window = new ImporterWindow();
$Window->display();