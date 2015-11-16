<?php
/**
 * This file contains the class of the RunalyzePluginStat "Tag".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Tag';

use Runalyze\Configuration;

/**
 * Class: RunalyzePluginStat_Tag
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Tag extends PluginStat {
    
	private $TagData = array();
	
	private $Tags = array();

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Tag analysis');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('How often have you tagged your activities with tag x?');
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
	    $this->setAnalysisNavigation();
		$this->initData();
	}
	
	private function setAnalysisNavigation() {
		$Factory = new \Runalyze\Model\Factory(SessionAccountHandler::getId());
		$this->Tags = $Factory->allTags();
		$LinkList = '<li class="with-submenu"><span class="link">' . __('Tags') . '</span><ul class="submenu">';
		foreach($this->Tags as $Tag) {
		    $LinkList .= '<li>' . $this->getInnerLink($Tag->tag(), false, false, $Tag->id()) . '</li>';
		}
		$LinkList .= '</ul></li>';

		$this->setToolbarNavigationLinks(array($LinkList));
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayData();
	}

	/**
	 * Display the table with summed data for every month 
	 */
	private function displayData() {
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>'.HTML::monthTr(8, 1).'</thead>';
		echo '<tbody>';

		if (empty($this->TagData)) {
			echo '<tr><td colspan="13" class="c"><em>'.__('No activities with tag x found.').'</em></td></tr>';
		}

		foreach ($this->TagData as $y => $Data) {
			echo '<tr><td class="b l">'.$y.'</td>';

			for ($m = 1; $m <= 12; $m++) {
				if (isset($Data[$m]) && $Data[$m]['num'] > 0) {
					echo '<td title="'.$Data[$m]['num'].'x">'.round(100*$Data[$m]['tag']/$Data[$m]['num']).' &#37;</td>';
				} else {
					echo HTML::emptyTD();
				}
			}

			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	
	
	/**
	 * Initialize $this->TagData
	 */
	private function initData() {
	    $UsersTags = array_map(function ($tag) { return $tag->id(); }, $this->Tags);
	    if($_GET['dat'] && is_numeric($_GET['dat']) && in_array($_GET['dat'], $UsersTags)) {
		$UsersTags = array_map(function ($tag) { return $tag->id(); }, $this->Tags);
		$result = DB::getInstance()->query('
			SELECT  
			    SUM(IF(tr.id IN (SELECT activityid FROM runalyze_activity_tag WHERE tagid='.$_GET['dat'].'),1,0)) as tag,
			    SUM(IF(tr.time,1,0)) as `num`,
			    YEAR(FROM_UNIXTIME(`tr`.`time`)) as `year`,
			    MONTH(FROM_UNIXTIME(`tr`.`time`)) as `month` 
			FROM `runalyze_training` tr
			    WHERE `accountid`='.SessionAccountHandler::getId().'
			GROUP BY `year` DESC, `month` ASC'
		)->fetchAll();
		
		foreach ($result as $dat) {
			if ($dat['tag'] > 0) {
				$this->TagData[$dat['year']][$dat['month']] = array('tag' => $dat['tag'], 'num' => $dat['num']);
			}
		}
	    }
	}
}