<?php
/**
 * File for displaying meta course for facebook
 * Call:   call.MeataCourse.php
 */
require_once '../inc/class.Frontend.php';
require_once '../inc/class.FrontendShared.php';

$Frontend = new FrontendShared(true);

$Meta = new HTMLMetaForFacebook();
$Meta->displayCourse();