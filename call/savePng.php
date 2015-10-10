<?php
header("Content-type: image/png");
header("Content-Disposition: attachment; filename=".strtolower(str_replace(' ', '_', $_POST['filename'])));

$encodeData = substr($_POST['image'], strpos($_POST['image'], ',') + 1);
echo base64_decode($encodeData);