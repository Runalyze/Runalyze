<?php
include('../../config/functions.php');
connect();

$dat_db = mysql_query('SELECT `arr_lat`, `arr_lon` FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($dat_db);

$arr_lat = explode('|', $dat['arr_lat']);
$arr_lon = explode('|', $dat['arr_lon']);
$arr_alt = array();
$num = count($arr_lat);

for ($i = 0; $i < $num; $i++) {
	if ($i%3 == 0) {
		$lat = $arr_lat[$i];
		if ($lat != 0) {
			$url = 'http://ws.geonames.org/srtm3?lat='.$lat.'&lng='.$arr_lon[$i];
			$dat = trim(@file_get_contents($url));
			$arr_alt[] = $dat;
			$arr_alt[] = $dat;
			$arr_alt[] = $dat;
		}
		else {
			$arr_alt[] = 0;
			$arr_alt[] = 0;
			$arr_alt[] = 0;
		}
	}
}

mysql_query('UPDATE `ltb_training` SET `arr_alt`="'.implode('|',$arr_alt).'" WHERE `id`="'.$_GET['id'].'" LIMIT 1');
echo('Success.');
?>