<h1>
	<span class="right linksWithMargin">
<?php $this->displayIconLinks(); ?>
	</span>

<?php $this->displayNavigationLinks(); ?>
</h1>

<div id="<?php echo self::$CALENDAR_ID; ?>">

	<div class="c">
		<?php echo self::getWeekLink('aktuelle Woche', time()); ?>
		- <?php echo self::getMonthLink('aktueller Monat', time()); ?>
		- <?php echo self::getYearLink('aktuelles Jahr', time()); ?>
	</div>

	<div id="widgetCalendar">
	</div>

	<span id="calendarResult" class="hide">W&auml;hle ein Datum aus ...</span>

	<div class="c">
		Mit zwei Klicks auf die Tage kann eine beliebige Zeitspanne ausgew&auml;hlt werden.
	</div>

	<div class="c">
		<span class="button" id="calendarSubmit">Auswahl anzeigen</span>
	</div>
</div>

<table id="dataBrowser">
	<tr class="space">
		<td colspan="<?php echo ($this->Dataset->column_count + 2); ?>" />
	</tr>
<?php
foreach ($this->days as $i => $day) {
	if (!empty($day['trainings'])) {
		foreach ($day['trainings'] as $t => $Training) {
			$id = $Training['id'];
			$wk_class = Helper::TrainingIsCompetition($id) ? ' wk' : '';
			echo('<tr class="a'.($i%2+1).' r training'.$wk_class.'" id="training_'.$id.'" '.Ajax::trainingLinkAsOnclick($id).'>');

			if ($t != 0)
				echo('<td colspan="2" />');
			else {
				echo('<td class="l" style="width:24px;">');

				foreach ($day['shorts'] as $short) {
					$this->Dataset->setTrainingId($short['id'], $short);
					$this->Dataset->displayShortLink();
				}

				echo('</td><td class="l">'.Dataset::getDateString($day['date']).'</td>');
			}

			$this->Dataset->setTrainingId($id, $Training);
			$this->Dataset->displayTableColumns();

			echo('</tr>');
		}
	} else {
		echo('
		<tr class="a'.($i%2+1).' r">
			<td class="l" style="width:24px;">');

		foreach ($day['shorts'] as $short) {
			$this->Dataset->setTrainingId($short['id'], $short);
			$this->Dataset->displayShortLink();
		}

		echo('</td>
			<td class="l">'.Dataset::getDateString($day['date']).'</td>
			<td colspan="'.$this->Dataset->column_count.'" />
		</tr>');
	}

	if (date("w", $day['date']) == 0 || $i == ($this->day_count-1))
		echo (NL.'
	<tr class="space">
		<td colspan="'.($this->Dataset->column_count+2).'" />
	</tr>'.NL);
}

// Z U S A M M E N F A S S U N G
echo '<tfoot>';
$sports = $this->Mysql->fetchAsArray('SELECT `id`, `time`, `sportid`, SUM(1) as `num` FROM `'.PREFIX.'training` WHERE `time` BETWEEN '.($this->timestamp_start-10).' AND '.($this->timestamp_end-10).' GROUP BY `sportid`');
foreach ($sports as $sportdata) {
	$Sport = new Sport($sportdata['sportid']);
	echo('
<tr class="a'.(($i++)%2+1).' r">
	<td colspan="2">
		<small>'.$sportdata['num'].'x</small>
		'.$Sport->name().'
	</td>');

	$this->Dataset->loadGroupOfTrainings($sportdata['sportid'], $this->timestamp_start, $this->timestamp_end);
	$this->Dataset->displayTableColumns();

	echo('
</tr>'.NL);
}
echo '</tfoot>';
?>

</table>