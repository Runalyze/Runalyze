	<div id="rechenspiele">
		<small class="right r">
<?php
$VDOT = $global['VDOT_form'];

$t = array();
$t[] = array('kurz' => 'RL', 'pVDOT' => '59-64');
$t[] = array('kurz' => 'DL', 'pVDOT' => '65-74');
$t[] = array('kurz' => 'LL', 'pVDOT' => '65-74');
$t[] = array('kurz' => 'TDL', 'pVDOT' => '83-88');
$t[] = array('kurz' => 'IT', 'pVDOT' => '95-100');
$t[] = array('kurz' => 'WHL', 'pVDOT' => '105-110');

foreach ($t as $train) {
	$train_tempo = explode('-',$train['pVDOT']);
	echo ('
'.$train['kurz'].': <em>'.jd_pace(jd_vVDOT($VDOT)*$train_tempo[1]/100).'</em> - <em>'.jd_pace(jd_vVDOT($VDOT)*$train_tempo[0]/100).'</em>/km<br />');
}
?>
		</small>
		<span class="left" style="width:60%;">
<?php
// TODO weitere Rechenspiele
echo('<p><span>'.round(100*ATL()/$config['max_atl']).' &#37;</span> <strong>M&uuml;digkeit</strong> <small>(ATL)</small></p>
<p><span>'.round(100*CTL()/$config['max_ctl']).' &#37;</span> <strong>Fitnessgrad</strong> <small>(CTL)</small></p>
<p><span>'.TSB().'</span> <strong>Stress Balance</strong> <small>(TSB)</small></p>
<p><span>'.round($global['VDOT_form'],2).'</span> <strong>VDOT</strong></p>
<p><span>'.grundlagenausdauer().'</span> <strong>Grundlagenausdauer</strong></p>');
?>
		</span>
		<br class="clear" />
	</div>