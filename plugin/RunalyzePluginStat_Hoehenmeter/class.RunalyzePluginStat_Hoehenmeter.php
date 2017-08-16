<?php
/**
 * This file contains the class of the RunalyzePluginStat "Hoehenmeter".
 * @package Runalyze\Plugins\Stats
 */

use Runalyze\Model\Activity;
use Runalyze\View\Activity\Linker;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Elevation;

$PLUGINKEY = 'RunalyzePluginStat_Hoehenmeter';
/**
 * Class: RunalyzePluginStat_Hoehenmeter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Hoehenmeter extends PluginStat {
	private $ElevationData = array();
	private $IsFirstChangeTable = true;

	final public function name() {
		return __('Elevation');
	}

	final public function description() {
		return __('Your steepest activities and an overview of your cumulated elevation.');
	}

	protected function prepareForDisplay() {
		$this->setSportsNavigation(true, true);
		$this->setYearsNavigation(true, true, true);

		$this->setHeaderWithSportAndYear();

		$this->initElevationData();
	}

	protected function defaultYear() {
		return -1;
	}

	protected function titleForAllYears() {
		return __('All years');
	}

	protected function displayContent() {
		if ($this->year == -1)
			$this->displayElevationData();

		$this->displayActivityRankings();

		echo HTML::clearBreak();
	}

	private function displayElevationData() {
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>'.HTML::monthTr(8, 1, 'td', true).'</thead>';
		echo '<tbody>';

		if (empty($this->ElevationData))
			echo '<tr><td colspan="13" class="l"><em>'.__('No routes found.').'</em></td></tr>';
		foreach ($this->ElevationData as $y => $Data) {
			echo('
				<tr>
					<td class="b l">'.$y.'</td>');
			$summarized = 0;
			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m]) && $Data[$m]['elevation'] > 0) {
					$Link = new SearchLink();
					$Link->fromTo(mktime(0,0,0,$m,1,$y), mktime(0,0,0,$m+1,0,$y));
					$Link->sortBy('elevation');
					$summarized += $Data[$m]['elevation'];

					echo '<td>'.$Link->link(Elevation::format($Data[$m]['elevation'])).'</td>';
				} else {
					echo HTML::emptyTD();
				}
			}
			echo '<td>'.Elevation::format($summarized).'</td>';


			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	private function displayActivityRankings() {
	    echo '<div class="c blocklist blocklist-change-menu blocklist-width-auto margin-top-4x">
                <a class="change triggered" href="#elevation-ranking-elevation" target="elevation-rankings"><strong><i class="fa fa-fw fa-area-chart"></i> '.__('Most elevation').'</strong></a>
                <a class="change" href="#elevation-ranking-grade" target="elevation-rankings"><strong><i class="fa fa-fw fa-line-chart"></i> '.__('Steepest routes').'</strong></a>
                <a class="change" href="#elevation-ranking-cs" target="elevation-rankings"><strong><i class="fa fa-fw fa-trophy"></i> '.__('Highest Climb Score').'</strong></a>
            </div>';

	    echo '<div id="elevation-rankings">';

        $this->displayData(
            __('Most elevation'), 'elevation',
            $this->getData('elevation'),
            function ($activityData) {
                $grade = $activityData['distance'] > 0 ? $activityData['elevation'] / $activityData['distance'] : 0;

                return Elevation::format($activityData['elevation']).'<br>
					<small>'.round($grade/10, 2).'&nbsp;&#37;,&nbsp;'.Distance::format($activityData['distance']).'</small>';
            }
        );

	    $this->displayData(
            __('Steepest routes'), 'grade',
            $this->getData('gradient'),
            function ($activityData) {
                return round($activityData['gradient']/10, 2).'&nbsp;&#37;<br>
					<small>'.Elevation::format($activityData['elevation']).',&nbsp;'.Distance::format($activityData['distance']).'</small>';
            }
        );

        $this->displayData(
            __('Highest Climb Score'), 'cs',
            $this->getData('climb_score'),
            function ($activityData) {
                return '<small>'.round($activityData['gradient']/10, 2).'&nbsp;&#37;<br>
					'.Elevation::format($activityData['elevation']).',&nbsp;'.Distance::format($activityData['distance']).'</small>';
            }
        );

        echo '</div>';
	}

    private function displayData($title, $changeId, array $data, $functionToDisplayElevation) {
        echo '<table class="fullwidth zebra-style more-padding change" id="elevation-ranking-'.$changeId.'"'.(!$this->IsFirstChangeTable ? ' style="display:none;"' : '').'>';
        echo '<thead><tr class="b c"><th colspan="4">'.$title.'</th><th><abbr title="Climb Score">CS</abbr></th></tr></thead>';
        echo '<tbody>';

        if (empty($data)) {
            echo '<tr><td colspan="5"><em>'.__('No routes found.').'</em></td></tr>';
        } else {
            foreach ($data as $activityData) {
                $Activity = new Activity\Entity($activityData);
                $Linker = new Linker($Activity);

                echo '<tr>
                    <td class="small">'.$Linker->weekLink().'</td>
                    <td>'.$Linker->linkWithSportIcon().'</td>
                    <td>'.$this->labelFor($activityData['route'], $activityData['title']).'</td>
                    <td class="r">
                        '.$functionToDisplayElevation($activityData).'</small>
                    </td>
                    <td class="r vc"><a class="window" href="activity/'.$activityData['id'].'/climb-score">'.$activityData['climb_score'].'</a></td>
                </tr>';
            }
        }

        echo '</tbody></table>';

        $this->IsFirstChangeTable = false;
    }

	/**
	 * Get label
	 * @param string $route
	 * @param string $title
	 * @return string
	 */
	private function labelFor($route, $title) {
		if ($route != '') {
			if ($title != '') {
				return $route.' (<em>'.$title.'</em>)';
			}

			return $route;
		} elseif ($title != '') {
			return '<em>'.$title.'</em>';
		}

		return '<em>'.__('unlabeled').'</em>';
	}

	private function initElevationData() {
		$result = DB::getInstance()->query('
			SELECT
				SUM(`elevation`) as `elevation`,
				SUM(`distance`) as `km`,
				YEAR(FROM_UNIXTIME(`time`)) as `year`,
				MONTH(FROM_UNIXTIME(`time`)) as `month`
			FROM `'.PREFIX.'training`
			WHERE `accountid`='.SessionAccountHandler::getId().' AND `elevation` > 0 '.$this->getSportAndYearDependenceForQuery().'
			GROUP BY `year`, `month`'
		)->fetchAll();

		foreach ($result as $dat) {
			$this->ElevationData[$dat['year']][$dat['month']] = array(
				'elevation' => $dat['elevation'],
				'km' => $dat['km'],
			);
		}
	}

	private function getData($order, $limit = 10) {
	    return DB::getInstance()->query('
			SELECT
				`'.PREFIX.'training`.`id`,
				`'.PREFIX.'training`.`time`,
				`'.PREFIX.'training`.`sportid`,
				`'.PREFIX.'training`.`title`,
				`'.PREFIX.'training`.`s`,
				`'.PREFIX.'training`.`climb_score`,
				`'.PREFIX.'training`.`percentage_hilly`,
				`'.PREFIX.'route`.`name` as `route`,
				`'.PREFIX.'route`.`distance`,
				`'.PREFIX.'route`.`elevation`,
				(`'.PREFIX.'route`.`elevation`/`'.PREFIX.'route`.`distance`) as `gradient`
			FROM `'.PREFIX.'training`
			LEFT JOIN `'.PREFIX.'route` ON `'.PREFIX.'training`.`routeid`=`'.PREFIX.'route`.`id`
			WHERE `'.PREFIX.'training`.`accountid`="'.SessionAccountHandler::getId().'" AND
				`'.PREFIX.'training`.`elevation` > 0 '.$this->getSportAndYearDependenceForQuery().'
			ORDER BY `'.$order.'` DESC
			LIMIT '.(int)$limit
        )->fetchAll();
    }
}
