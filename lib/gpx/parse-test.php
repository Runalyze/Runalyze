<?php
require_once('parser.inc.php');

if (!isset($_GET['f']) || !isset($_GET['id']))
	die('$_GET[\'f\'] muss gesetzt sein (z.B. ...php?f=2010-07-29_1143&id=1143!');
$xml = tcxLoad('tcx/'.$_GET['f'].'.tcx');

$i = 0;
$starttime = 0;
$time = array();
$latitude = array();
$longitude = array();
$altitude = array();
$distance = array();
$heartrate = array();
$pace = array();

echo('Aktivit&auml;t ('.$xml['trainingcenterdatabase']['activities']['activity']['attr']['Sport'].', '.$xml['trainingcenterdatabase']['activities']['activity']['id']['value'].')<br />');

foreach($xml['trainingcenterdatabase']['activities']['activity']['lap'] as $lap) {
	$i++;
	echo($i.'. Runde: '.round($lap['distancemeters']['value']/1000,3).' km in '.floor($lap['totaltimeseconds']['value']/60).':'.($lap['totaltimeseconds']['value']%60).'; '.$lap['calories']['value'].' kcal; &Oslash; '.$lap['averageheartratebpm']['value']['value'].' bpm, max. '.$lap['maximumheartratebpm']['value']['value'].' bpm<br />');
	
	foreach($lap['track'] as $track) {
		if (isset($track['trackpoint']))
			$trackpointArray = $track['trackpoint'];
		else
			$trackpointArray = $track;
		foreach($trackpointArray as $trackpoint) {
			if (isset($trackpoint['distancemeters'])) {
				if ($starttime == 0)
					$starttime = strtotime($trackpoint['time']['value']);
				$time[] = strtotime($trackpoint['time']['value']) - $starttime;
				$distance[] = round($trackpoint['distancemeters']['value'])/1000;
				$pace[] = ((end($distance) - prev($distance)) != 0)
					? round((end($time) - prev($time)) / (end($distance) - prev($distance)))
					: 0;
				if (isset($trackpoint['position'])) {
					$latitude[] = $trackpoint['position']['latitudedegrees']['value'];
					$longitude[] = $trackpoint['position']['longitudedegrees']['value'];
				} else {
					$latitude[] = 0;
					$longitude[] = 0;
				}
				$altitude[] = (isset($trackpoint['altitudemeters']))
					? round($trackpoint['altitudemeters']['value'])
					: 0;
				$heartrate[] = (isset($trackpoint['heartratebpm']))
					? $trackpoint['heartratebpm']['value']['value']
					: 0;
			} else { // Delete pause from timeline
				$starttime += (strtotime($trackpoint['time']['value'])-$starttime) - end($time);
			}
		}
	}
	//print_r($lap);
}

echo ('<input type="text" value="'.implode('|',$time).'" /><br />');
echo ('<input type="text" value="'.implode('|',$latitude).'" /><br />');
echo ('<input type="text" value="'.implode('|',$longitude).'" /><br />');
echo ('<input type="text" value="'.implode('|',$altitude).'" /><br />');
echo ('<input type="text" value="'.implode('|',$distance).'" /><br />');
echo ('<input type="text" value="'.implode('|',$heartrate).'" /><br />');
echo ('<input type="text" value="'.implode('|',$pace).'" /><br />');

mysql_connect('localhost', 'd0033d80', 'fc683f6a');
mysql_select_db('ltb');

mysql_query('UPDATE `ltb_training` SET
`arr_time`="'.implode('|',$time).'",
`arr_lat`="'.implode('|',$latitude).'",
`arr_lon`="'.implode('|',$longitude).'",
`arr_alt`="'.implode('|',$altitude).'",
`arr_dist`="'.implode('|',$distance).'",
`arr_heart`="'.implode('|',$heartrate).'",
`arr_pace`="'.implode('|',$pace).'"
WHERE `id`='.$_GET['id'].' LIMIT 1');
echo mysql_error();
?>