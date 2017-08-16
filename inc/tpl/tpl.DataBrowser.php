<?php

use Runalyze\Util\LocalTime;

?>
<div class="panel-heading">
	<div class="panel-menu">
		<?php $this->displayIconLinks(); ?>
	</div>
	<div class="icons-left"><?php $this->displayNavigationLinks(); ?></div>
	<h1><?php $this->displayTitle(); ?></h1>
	<div class="hover-icons"><?php $this->displayHoverLinks(); ?></div>
</div>
<div class="panel-content">
	<div id="<?php echo DataBrowser::CALENDAR_ID; ?>">

		<div id="widget-calendar">
		</div>

		<span id="calendar-result" class="hide"><?php _e('Choose a date ...'); ?></span>
		<input id="calendar-start" type="hidden" value="<?php echo $this->TimestampStart; ?>000">
		<input id="calendar-end" type="hidden" value="<?php echo $this->TimestampEnd; ?>000">

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

<?php
if ($this->DayCount < 25) {
	$weekSeparator = ' top-separated';
	$monthSeparator = ' top-separated-light';
} else {
	$weekSeparator = ' top-separated-light';
	$monthSeparator = ' top-separated';
}

$Context = new \Runalyze\Dataset\Context(new Runalyze\Model\Activity\Entity(), $this->AccountID);
$Table = new \Runalyze\View\Dataset\Table($this->DatasetConfig);
?>

	<div id="data-browser-container">
		<table class="zebra-style">
			<?php if (\Runalyze\Configuration::DataBrowser()->showLabels()): ?>
			<thead class="data-browser-labels">
				<tr class="small">
					<td colspan="<?php echo $this->AdditionalColumns; ?>"></td>
					<?php echo $Table->codeForColumnLabels(); ?>
				</tr>
			</thead>
			<?php endif; ?>
			<tbody class="top-and-bottom-border">
<?php
$currentWeek = null;
$currentMonth = null;
foreach ($this->Days as $i => $day) {
	$trClass = '';
	$week = (int)\Runalyze\Configuration::General()->weekStart()->phpWeek($day['date'], true);
	$month = (int)LocalTime::date('n', $day['date']);

	if ($i > 0 && $week != $currentWeek) {
		$trClass = $weekSeparator;
	}

	if ($i > 0 && $month != $currentMonth) {
		$trClass = ($trClass == '') ? $monthSeparator : ' top-separated';
	}

	if (!empty($day['trainings'])) {
		foreach ($day['trainings'] as $t => $Training) {
			$id       = $Training['id'];
			$Sport = $this->Factory->sport($id);
			$wk_class = ($Training['is_race']) ? ' wk' : '';
			$trClass = ($t == 0) ? $trClass : '';

			if (FrontendShared::$IS_SHOWN && !$Training['is_public']) {
				echo '<tr class="r'.$trClass.' training'.$wk_class.'">';
			} else {
				echo '<tr class="r'.$trClass.' training'.$wk_class.'" id="training_'.$id.'" '.Ajax::trainingLinkAsOnclick($id).'>';
			}

			if ($t != 0) {
				echo '<td colspan="2"></td>';
			} else {
				echo '<td class="l" style="width:24px;">';

				foreach ($day['shorts'] as $short) {
					$Context->setActivityData($short);

					if (FrontendShared::$IS_SHOWN && !$Context->activity()->isPublic()) {
                        echo $Table->codeForShortLink($Context);
                    } else {
                        echo '<span class="link" '.Ajax::trainingLinkAsOnclick($Context->activity()->id()).'>'.$Table->codeForShortLink($Context).'</span>';
                    }
				}

				echo '</td><td class="l as-small-as-possible">'.$this->dateString($day['date']).'</td>';
			}

			$Context->setActivityData($Training);

			echo $this->codeForAdditionalColumnsForActivity($Table, $Context);
			echo $Table->codeForColumns($Context);

			echo '</tr>';
		}

		$currentWeek = $week;
		$currentMonth = $month;
	} elseif (!\Runalyze\Configuration::DataBrowser()->showActiveDaysOnly() || !empty($day['shorts'])) {
		echo '
			<tr class="r'.$trClass.'">
				<td class="l" style="width:24px;">';

		foreach ($day['shorts'] as $short) {
            $Context->setActivityData($short);

            if (FrontendShared::$IS_SHOWN && !$Context->activity()->isPublic()) {
                echo $Table->codeForShortLink($Context);
            } else {
                echo '<span class="link" '.Ajax::trainingLinkAsOnclick($Context->activity()->id()).'>'.$Table->codeForShortLink($Context).'</span>';
            }
		}

		$cols = $Table->numColumns();

		echo '</td>
				<td class="l as-small-as-possible">'.$this->dateString($day['date']).'</td>
				<td colspan="'.($Table->numColumns() + $this->AdditionalColumns - 2).'"></td>
			</tr>';

		$currentWeek = $week;
		$currentMonth = $month;
	}
}

if ($this->AllDaysEmpty && \Runalyze\Configuration::DataBrowser()->showActiveDaysOnly()) {
    echo '<tr><td colspan="'.($Table->numColumns() + $this->AdditionalColumns).'"><em>'.__('There are no activities for this time range.').'</em></td></tr>';
}

echo '</tbody>';
echo '<tbody>';

$Summary = $this->DatasetQuery->fetchSummaryForAllSport($this->TimestampStart, $this->TimestampEnd);

foreach ($Summary as $data) {
	$Sport = $this->Factory->sport($data['sportid']);
	$Context->setActivityData($data);

	echo '<tr class="r no-zebra">';
	echo '<td colspan="'.$this->AdditionalColumns.'"><small>'.$data['num'].'x</small> '.$Sport->name().'</td>';
	echo $Table->codeForColumns($Context, array(Runalyze\Dataset\Keys::SPORT));
	echo '</tr>';
}
?>
			</tbody>
		</table>
	</div>
</div>
