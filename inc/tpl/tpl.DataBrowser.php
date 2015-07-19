<div class="panel-heading">
	<div class="panel-menu">
		<?php $this->displayIconLinks(); ?>
	</div>
	<div class="icons-left"><?php $this->displayNavigationLinks(); ?></div>
	<h1><?php $this->displayTitle(); ?></h1>
	<div class="hover-icons"><?php $this->displayHoverLinks(); ?></div>
</div>
<div class="panel-content">
	<div id="<?php echo DataBrowser::$CALENDAR_ID; ?>">

		<div id="widget-calendar">
		</div>

		<span id="calendar-result" class="hide"><?php _e('Choose a date ...'); ?></span>
		<input id="calendar-start" type="hidden" value="<?php echo $this->timestamp_start; ?>000">
		<input id="calendar-end" type="hidden" value="<?php echo $this->timestamp_end; ?>000">

        <input id="calendar-locale" type="hidden" value="<?php

        $days = array(__("Sunday"), __("Monday"), __("Tuesday"), __("Wednesday"), __("Thursday"), __("Friday"), __("Saturday"));
        $daysMin = array(__("Su"), __("Mo"), __("Tu"), __("We"), __("Th"), __("Fr"), __("Sa"));
        $daysShort = array(__("Sun"), __("Mon"), __("Tue"), __("Wed"), __("Thu"), __("Fri"), __("Sat"));
        $months = array(__("January"), __("February"), __("March"), __("April"), __("May"), __("June"),
            __("July"), __("August"), __("September"), __("October"), __("November"), __("December"));
        $monthsShort = array(__("Jan"), __("Feb"), __("Mar"), __("Apr"), __("May"), __("Jun"), __("Jul"), __("Aug"), __("Sep"), __("Oct"), __("Nov"), __("Dec"),);
        $weekMin=_("wk");

        $locale = array ("days" => $days, "daysMin"=>$daysMin, "daysShort"=>$daysShort, "months" => $months, "monthsShort" => $monthsShort, "weekMin" => $weekMin );

        echo htmlspecialchars(json_encode($locale)) ?>">


        <div class="c">
			<?php _e('You can select any time range by selecting two dates.'); ?>
		</div>

		<div class="c">
			<span class="button" id="calendar-submit"><?php _e('Show selection'); ?></span>
		</div>
	</div>

	<div id="data-browser-container">
		<table class="zebra-style">
			<?php if (\Runalyze\Configuration::DataBrowser()->showLabels()): ?>
			<thead class="data-browser-labels">
				<tr class="small">
					<td colspan="<?php echo (2 + $this->showPublicLink); ?>"></td>
					<?php $this->Dataset->displayTableLabels(); ?>
				</tr>
			</thead>
			<?php endif; ?>
			<tbody class="top-and-bottom-border">
<?php
foreach ($this->days as $i => $day) {
	if (!empty($day['trainings'])) {
		foreach ($day['trainings'] as $t => $Training) {
			$id       = $Training['id'];
			$wk_class = isset($Training['typeid']) && $Training['typeid'] == \Runalyze\Configuration::General()->competitionType() ? ' wk' : '';

			if (FrontendShared::$IS_SHOWN && !$Training['is_public'])
				echo '<tr class="r training'.$wk_class.'">';
			else
				echo '<tr class="r training'.$wk_class.'" id="training_'.$id.'" '.Ajax::trainingLinkAsOnclick($id).'>';

			if ($t != 0)
				echo '<td colspan="2"></td>';
			else {
				echo '<td class="l" style="width:24px;">';

				foreach ($day['shorts'] as $short) {
					$this->Dataset->setActivityData($short);
					$this->Dataset->displayShortLink();
				}

				echo '</td><td class="l as-small-as-possible">'.Dataset::getDateString($day['date']).'</td>';
			}

			$this->Dataset->setActivityData($Training);

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
			$this->Dataset->setActivityData($short);
			$this->Dataset->displayShortLink();
		}

		echo '</td>
				<td class="l as-small-as-possible">'.Dataset::getDateString($day['date']).'</td>
				<td colspan="'.($this->Dataset->cols() + $this->showPublicLink).'"></td>
			</tr>';
	}
}

echo '</tbody>';
echo '<tbody>';

// Z U S A M M E N F A S S U N G
$WhereNotPrivate = (FrontendShared::$IS_SHOWN && !\Runalyze\Configuration::Privacy()->showPrivateActivitiesInList()) ? 'AND is_public=1' : '';
$sports = DB::getInstance()->query('SELECT `id`, `time`, `sportid`, SUM(1) as `num` FROM `'.PREFIX.'training` WHERE `time` BETWEEN '.($this->timestamp_start-10).' AND '.($this->timestamp_end-10).' AND accountid = '.SessionAccountHandler::getId().' '.$WhereNotPrivate.' GROUP BY `sportid`')->fetchAll();
foreach ($sports as $i => $sportdata) {
	$Sport = new Sport($sportdata['sportid']);
	echo '<tr class="r no-zebra">
			<td colspan="'.$this->additionalColumns.'">
				<small>'.$sportdata['num'].'x</small>
				'.$Sport->name().'
			</td>';

	$this->Dataset->loadGroupOfTrainings($sportdata['sportid'], $this->timestamp_start, $this->timestamp_end);
	$this->Dataset->displayTableColumns();

	echo '
		</tr>';
}
?>
			</tbody>
		</table>
	</div>
</div>