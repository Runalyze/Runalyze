<?php
/**
 * File for displaying meta course for facebook
 * Call:   call.MeataCourse.php
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend(true);

$Meta = new HTMLMetaForFacebook();
$Meta->displayCourse();