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
		$this->AllSports = DB::getInstance()
			->query('SELECT `id`, `name` FROM `'.PREFIX.'sport` WHERE `accountid`="'.SessionAccountHandler::getId().'" ORDER BY `id` ASC')
			->fetchAll();
		$Options = array();

		foreach ($this->AllSports as $data) {
			$Options[$data['id']] = $data['name'];
		}

		$Sports = new PluginConfigurationValueSelect('sport', __('Sport to summarise tags for'));
		$Sports->setOptions($Options);

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($Sports);

		if (isset($_GET['sport']) && isset($Options[$_GET['sport']])) {
			$Configuration->object('sport')->setValue($_GET['sport']);
			$Configuration->update('sport');
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

		foreach ($this->AllSports as $Sport) {
			$active = $Sport['id'] == (int)$this->Configuration()->value('sport');
			$SportLinks[] = '<li'.($active ? ' class="active"' : '').'>'.Ajax::link($Sport['name'], 'panel-'.$this->id(), Plugin::$DISPLAY_URL.'/'.$this->id().'?sport='.$Sport['id']).'</li>';

			if ($active) {
				$CurrentSport = $Sport['name'];
			}
		}

		$Links = '<li class="with-submenu"><span class="link">'.$CurrentSport.'</span>';
		$Links .= '<ul class="submenu">'.implode('', $SportLinks).'</ul>';
		$Links .= '</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$Factory = new Model\Factory(SessionAccountHandler::getId());
		$Sport = $Factory->sport((int)$this->Configuration()->value('sport'));

		echo $this->getStyle();
		echo '<div id="equipment">';
		$this->showListFor($Sport);
		echo '</div>';

		echo HTML::clearBreak();
	}

	/**
	 * @param \Runalyze\Model\Sport\Entity $Sport
	 */
	protected function showListFor(Model\Sport\Entity $Sport) {
		$max = 0;
		$countTagsForSport = DB::getInstance()
			->query('SELECT '.PREFIX.'activity_tag.tagid, '.PREFIX.'tag.tag, COUNT(tagid) as `count`'.
				' FROM '.PREFIX.'activity_tag'.
				' LEFT JOIN '.PREFIX.'training ON '.PREFIX.'training.sportid = '.$Sport->id().
				' LEFT JOIN '.PREFIX.'tag ON '.PREFIX.'tag.id = '.PREFIX.'activity_tag.tagid'.
				' WHERE '.PREFIX.'training.id = activityid GROUP BY tagid ORDER BY count DESC');

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
		return '<style type="text/css">.equipment-usage { position: absolute; bottom: 0; left: 0; background-image:url(assets/images//damage.png); background-position:left center; height: 2px; max-width: 100%; }</style>';
	}

	/**
	 * Get shoe usage image
	 * @param float $percentage [0.0 .. 1.0]
	 * @return string
	 */
	protected function getUsageImage($percentage) {
		return '<span class="equipment-usage" style="width:'.round($percentage * 330).'px;"></span>';
	}
}
