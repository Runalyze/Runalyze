<?php
header("Content-type: image/png");
header("Content-Disposition: attachment; filename=".strtolower(str_replace(' ', '_', $_POST['filename'])));

echo file_get_contents($_POST['image']); 
?>