<div class="panel-heading">
	<div class="panel-menu">
		<?php $this->displayIconLinks(); ?>
	</div>
	<div class="icons-left"><?php $this->displayNavigationLinks(); ?></div>
	<h1><?php $this->displayTitle(); ?></h1>
</div>
<div class="panel-content">
	<div id="<?php echo DataBrowser::$CALENDAR_ID; ?>">

		<div id="widget-calendar">
		</div>

		<span id="calendar-result" class="hide">W&auml;hle ein Datum aus ...</span>
		<input id="calendar-start" type="hidden" value="<?php echo $this->timestamp_start; ?>000" />
		<input id="calendar-end" type="hidden" value="<?php echo $this->timestamp_end; ?>000" />

		<div class="c">
			Mit zwei Klicks auf die Tage kann eine beliebige Zeitspanne ausgew&auml;hlt werden.
		</div>

		<div class="c">
			<span class="button" id="calendar-submit">Auswahl anzeigen</span>
		</div>
	</div>

	<div id="data-browser-container">
		<table class="zebra-style">
			<tbody class="top-and-bottom-border">
<?php
foreach ($this->days as $i => $day) {
	if (!empty($day['trainings'])) {
		foreach ($day['trainings'] as $t => $Training) {
			$id       = $Training['id'];
			$wk_class = isset($Training['typeid']) && $Training['typeid'] == CONF_WK_TYPID ? ' wk' : '';

			if (FrontendShared::$IS_SHOWN && !$Training['is_public'])
				echo '<tr class="r training'.$wk_class.'">';
			else
				echo '<tr class="r training'.$wk_class.'" id="training_'.$id.'" '.Ajax::trainingLinkAsOnclick($id).'>';

			if ($t != 0)
				echo '<td colspan="2" />';
			else {
				echo '<td class="l" style="width:24px;">';

				foreach ($day['shorts'] as $short) {
					$this->Dataset->setTrainingId($short['id'], $short);
					$this->Dataset->displayShortLink();
				}

				echo '</td><td class="l">'.Dataset::getDateString($day['date']).'</td>';
			}

			$this->Dataset->setTrainingId($id, $Training);

			if ($this->showPublicLink)
				$this->Dataset->displayPublicIcon();

			$this->Dataset->displayTableColumns();

			echo '</tr>';
		}
	} else {
		echo '
			<tr class="r">
				<td class="l" style="width:24px;">';

		foreach ($day['shorts'] as $short) {
			$this->Dataset->setTrainingId($short['id'], $short);
			$this->Dataset->displayShortLink();
		}

		echo '</td>
				<td class="l">'.Dataset::getDateString($day['date']).'</td>
				<td colspan="'.($this->Dataset->cols() + $this->showPublicLink).'" />
			</tr>';
	}
}

echo '</tbody>';
echo '<tbody>';

// Z U S A M M E N F A S S U N G
$WhereNotPrivate = (FrontendShared::$IS_SHOWN && !CONF_TRAINING_LIST_ALL) ? 'AND is_public=1' : '';
$sports = $this->Mysql->fetchAsArray('SELECT `id`, `time`, `sportid`, SUM(1) as `num` FROM `'.PREFIX.'training` WHERE `time` BETWEEN '.($this->timestamp_start-10).' AND '.($this->timestamp_end-10).' '.$WhereNotPrivate.' GROUP BY `sportid`');
foreach ($sports as $i => $sportdata) {
	$Sport = new Sport($sportdata['sportid']);
	echo '
		<tr class="r no-zebra">
			<td colspan="'.$this->additionalColumns.'">
				<small>'.$sportdata['num'].'x</small>
				'.$Sport->name().'
			</td>';

	$this->Dataset->loadGroupOfTrainings($sportdata['sportid'], $this->timestamp_start, $this->timestamp_end);
	$this->Dataset->displayTableColumns();

	echo '
		</tr>'.NL;
}
?>
			</tbody>
		</table>
	</div>
</div>