<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Tags Summary".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_TagsSummary';

use Runalyze\Model;

/**
 * Class: RunalyzePluginPanel_TagsSummary
 *
 * @author Felix Gertz
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_TagsSummary extends PluginPanel {
	/**
	 * Internal array with all tags from database
	 * @var array
	 */
	private $Tags = null;

	/**
	 * @var array
	 */
	protected $AllSports = array();

	/**
	 * Timescale in months.
	 * @var array
	 */
	protected $Timescale = array('1' => '1', '3' => '3', '6' => '6', '12' => '12', '24' => '24');

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Tags summary');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Summarises your tags for the selected sport.');
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id());

		foreach (\Runalyze\Context::Factory()->allSports() as $sport) {
    	$this->AllSports[$sport->id()] = $sport->name();
		}

		$Sports = new PluginConfigurationValueSelect('sport', __('Sport to summarise tags for'));
		$Sports->setOptions($this->AllSports);

		$Configuration->addValue($Sports);

		if (isset($_GET['sport']) && isset($this->AllSports[$_GET['sport']])) {
			$Configuration->object('sport')->setValue($_GET['sport']);
			$Configuration->update('sport');
			Cache::delete(PluginConfiguration::CACHE_KEY);
		}

		$Timescale = new PluginConfigurationValueSelect('tagssummary_timescale', __('Show tags for this period of months'));
		$Timescale->setOptions($this->Timescale);

		$Configuration->addValue($Timescale);

		if (isset($_GET['tagssummary_timescale']) && in_array($_GET['tagssummary_timescale'], $this->Timescale)) {
			$Configuration->object('tagssummary_timescale')->setValue($_GET['tagssummary_timescale']);
			$Configuration->update('tagssummary_timescale');
			Cache::delete(PluginConfiguration::CACHE_KEY);
		}

		$this->setConfiguration($Configuration);
	}

	/**
	 * Method for getting the right symbol(s)
	 */
	protected function getRightSymbol() {
		$CurrentSport = '';
		$SportLinks = [];
		$CurrentTimescale = '';
		$TimescaleLinks = [];

		foreach ($this->AllSports as $id => $name) {
			$active = $id == (int)$this->Configuration()->value('sport');
			$SportLinks[] = '<li'.($active ? ' class="active"' : '').'>'.Ajax::link($name, 'panel-'.$this->id(), Plugin::$DISPLAY_URL.'/'.$this->id().'?sport='.$id).'</li>';

			if ($active) {
				$CurrentSport = $name;
			}
		}

		foreach (array_keys($this->Timescale) as $timescale) {
			$active = $timescale == $this->Configuration()->value('tagssummary_timescale');
			$name = $timescale.' '.($timescale != 1 ? __('months') : __('month'));
			$TimescaleLinks[] = '<li'.($active ? ' class="active"' : '').'>'.Ajax::link($name, 'panel-'.$this->id(), Plugin::$DISPLAY_URL.'/'.$this->id().'?tagssummary_timescale='.$timescale).'</li>';

			if ($active) {
				$CurrentTimescale = $name;
			}
		}

		$Links = '<li class="with-submenu"><span class="link">'.$CurrentSport.'</span>';
		$Links .= '<ul class="submenu">'.implode('', $SportLinks).'</ul>';
		$Links .= '</li>';

		$Links .= '<li class="with-submenu"><span class="link">'.$CurrentTimescale.'</span>';
		$Links .= '<ul class="submenu">'.implode('', $TimescaleLinks).'</ul>';
		$Links .= '</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$Factory = \Runalyze\Context::Factory();
		$Sport = $Factory->sport((int)$this->Configuration()->value('sport'));
		$timescale = $this->Configuration()->value('tagssummary_timescale');

		echo $this->getStyle();
		echo '<div id="tags-summary">';
		$this->showListFor($Sport, $timescale);
		echo '</div>';

		echo HTML::clearBreak();
	}

	/**
	 * @param \Runalyze\Model\Sport\Entity $Sport
	 */
	protected function showListFor(Model\Sport\Entity $Sport, $timescale) {
		$max = 0;
		$countTagsForSport = DB::getInstance()
			->query('SELECT activity_tag.tagid, tag.tag, COUNT(tagid) as `count`'.
				' FROM '.PREFIX.'activity_tag activity_tag'.
				' LEFT JOIN '.PREFIX.'training training ON training.sportid = '.$Sport->id().
				' LEFT JOIN '.PREFIX.'tag tag ON tag.id = activity_tag.tagid'.
				' WHERE training.id = activityid'.
				' AND FROM_UNIXTIME(training.time) >= NOW()-INTERVAL '.(int)$timescale.' MONTH'.
				' GROUP BY tagid ORDER BY count DESC');

		foreach ($countTagsForSport as $data) {
			if ($max == 0) {
				$max = (int)$data['count'];
			}

			echo '<p style="position:relative;">
				<span class="right">'.$data['count'].'x</span>
				<strong>'.SearchLink::to('tagid', $data['tagid'], $data['tag']).'</strong>
				'.$this->getUsageImage((int)$data['count'] / $max).'
			</p>';
		}

		if ($countTagsForSport->rowCount() == 0)
			echo HTML::em( __('You don\'t have any tags for this sport.') );
	}

	/**
	 * Get style
	 * @return string
	 */
	protected function getStyle() {
		return '<style type="text/css">.tags-usage { position: absolute; bottom: 0; left: 0; background-image:url(assets/images/damage.png); background-position:left center; height: 2px; max-width: 100%; }</style>';
	}

	/**
	 * Get shoe usage image
	 * @param float $percentage [0.0 .. 1.0]
	 * @return string
	 */
	protected function getUsageImage($percentage) {
		return '<span class="tags-usage" style="width:'.round($percentage * 330).'px;"></span>';
	}
}
