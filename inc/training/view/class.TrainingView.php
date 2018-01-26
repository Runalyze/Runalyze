<?php
/**
 * This file contains class::TrainingView
 * @package Runalyze\DataObjects\Training\View
 */

use Runalyze\Configuration;
use Runalyze\Export;
use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Linker;
use Runalyze\Model;

/**
 * Display a training
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TrainingView {
	/**
	 * Sections
	 * @var TrainingViewSection[]
	 */
	protected $Sections = array();

	/**
	 * Toolbar links
	 * @var array
	 */
	protected $ToolbarLinks = array();

	/**
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context;

	/**
	 * @param \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Context $context) {
		$this->Context = $context;

		$this->initToolbarLinks();
		$this->initSections();
	}

	/**
	 * Init toolbar links
	 */
	private function initToolbarLinks() {
		$Linker = new Linker($this->Context->activity());

		if (!Request::isOnSharedPage()) {
			$this->initShareLinks($Linker);
			$this->initExportLinks();
			$this->initToolsLinks();
			$this->initEditLinks($Linker);
		}

		$this->ToolbarLinks[] = Ajax::tooltip($Linker->weekLink(), '<em>'.__('Show week').'</em><br>'.$this->Context->dataview()->weekday().', '.$this->Context->dataview()->dateAndDaytime());
	}

	/**
	 * Init social share links
	 * @param \Runalyze\View\Activity\Linker $linker
	 */
	protected function initShareLinks(Linker $linker) {
		$this->ToolbarLinks[] = '<li class="with-submenu"><span class="link"><i class="fa fa-fw fa-share-alt"></i> '.__('Share').'</span><ul class="submenu">';

		if ($this->Context->activity()->isPublic()) {
			$this->ToolbarLinks[] = '<li><a href="'.$linker->publicUrl().'" target="_blank">'.Icon::$ATTACH.' '.__('Public link').'</a></li>';
		}

		foreach (Export\Share\Types::getEnum() as $typeid) {
			$Exporter = Export\Share\Types::get($typeid, $this->Context);

			if ($Exporter->isPossible()) {
				$link = '<li><a href="'.$Exporter->url().'"'.($Exporter->isExternalLink() ? ' target="_blank"' : '').'><i class="fa fa-fw '.$Exporter->iconClass().'"></i> '.$Exporter->name().'</a></li>';
				$this->ToolbarLinks[] = !$Exporter->isExternalLink() ? Ajax::window($link) : $link;
			}
		}

		$this->ToolbarLinks[] = '</ul></li>';
	}

	/**
	 * Init download links
	 */
	protected function initExportLinks() {
		$this->ToolbarLinks[] = '<li class="with-submenu"><span class="link"><i class="fa fa-fw fa-download"></i> '.__('Export').'</span><ul class="submenu">';

		foreach (Export\File\Types::getEnum() as $typeid) {
			$Exporter = Export\File\Types::get($typeid, $this->Context);

			if ($Exporter->isPossible()) {
				$this->ToolbarLinks[] = '<li><a href="'.$Exporter->url().'"><i class="fa fa-fw '.$Exporter->iconClass().'"></i> '.sprintf(__('as %s'), strtoupper($Exporter->extension())).'</a></li>';
			}
		}

		$this->ToolbarLinks[] = '</ul></li>';
	}

	protected function initToolsLinks() {
		$toolsLinks = [];

		if (
			$this->Context->hasTrackdata() &&
			$this->Context->trackdata()->has(Model\Trackdata\Entity::DISTANCE) &&
			$this->Context->trackdata()->has(Model\Trackdata\Entity::TIME)
		) {
			$toolsLinks[] = '<li><a class="window link" data-size="big" href="activity/'.$this->Context->activity()->id().'/splits-info"><i class="fa fa-fw fa-bar-chart"></i> '.__('Analyze splits').'</a></li>';
		}

		if ($this->Context->activity()->vo2maxByHeartRate() > 0) {
			$toolsLinks[] = '<li><a class="window link" data-size="small" href="activity/'.$this->Context->activity()->id().'/vo2max-info"><i class="fa fa-fw fa-calculator"></i> '.__('Show VO<sub>2</sub>max calculation').'</a></li>';
		}

		if ($this->Context->hasRoute() && $this->Context->route()->hasElevations()) {
			$toolsLinks[] = '<li><a class="window link" data-size="normal" href="activity/'.$this->Context->activity()->id().'/elevation-info"><i class="fa fa-fw fa-area-chart"></i> '.__('More about elevation').'</a></li>';
		}

        if ($this->Context->hasTrackdata() && $this->Context->trackdata()->has(Model\Trackdata\Entity::TIME) && $this->Context->trackdata()->has(Model\Trackdata\Entity::DISTANCE) && $this->Context->hasRoute() && $this->Context->route()->hasElevations()) {
            $toolsLinks[] = '<li><a class="window link" data-size="normal" href="activity/'.$this->Context->activity()->id().'/climb-score"><i class="fa fa-fw fa-area-chart"></i> '.__('Climb score').'</a></li>';
        }

		if ($this->Context->hasTrackdata() && $this->Context->trackdata()->has(Model\Trackdata\Entity::TIME)) {
			$toolsLinks[] = '<li><a class="window link" data-size="big" href="activity/'.$this->Context->activity()->id().'/time-series-info"><i class="fa fa-fw fa-line-chart"></i> '.__('Analyze time series').'</a></li>';
		}

		if ($this->Context->hasTrackdata() && $this->Context->trackdata()->has(Model\Trackdata\Entity::TIME) && $this->Context->trackdata()->has(Model\Trackdata\Entity::DISTANCE)) {
			$toolsLinks[] = '<li><a class="window link" data-size="big" href="activity/'.$this->Context->activity()->id().'/sub-segments-info"><i class="fa fa-fw fa-bar-chart"></i> '.__('Find best sub segments').'</a></li>';
		}

		if (!empty($toolsLinks)) {
			$this->ToolbarLinks = array_merge(
				$this->ToolbarLinks,
				['<li class="with-submenu"><span class="link"><i class="fa fa-fw fa-magic"></i> '.__('Tools').'</span><ul class="submenu">'],
				$toolsLinks,
				['</ul></li>']
			);
		}
	}

	/**
	 * Init edit links
	 * @param \Runalyze\View\Activity\Linker $linker
	 */
	protected function initEditLinks(Linker $linker) {
		if ($this->Context->activity()->isPublic()) {
			$privacyLabel = __('Make private');
			$privacyIcon = 'fa-lock';
		} else {
			$privacyLabel = __('Make public');
			$privacyIcon = 'fa-unlock';
		}

		if ($this->Context->hasRaceResult()) {
			$raceResultLabel = __('Edit race result');
		} else {
			$raceResultLabel = __('Add race result');
		}

		$this->ToolbarLinks[] = '<li class="with-submenu"><span class="link"><i class="fa fa-fw fa-wrench"></i></span><ul class="submenu">';
		$this->ToolbarLinks[] = '<li>'.Ajax::window('<a class="link" href="'.$linker->editUrl().'">'.Icon::$EDIT.' '.__('Edit activity').'</a> ','small').'</li>';
		$this->ToolbarLinks[] = '<li>'.Ajax::window('<a class="link" href="my/raceresult/'.$this->Context->activity()->id().'"><i class="fa fa-fw fa-trophy"></i> '.$raceResultLabel.'</a></li></a> ','normal').'</li>';
		$this->ToolbarLinks[] = '<li><a class="ajax" target="statistics-inner" href="activity/'.$this->Context->activity()->id().'?action=changePrivacy"><i class="fa fa-fw '.$privacyIcon.'"></i> '.$privacyLabel.'</a></li>';
		$this->ToolbarLinks[] = '<li><a class="ajax" target="statistics-inner" href="activity/'.$this->Context->activity()->id().'/delete" data-confirm="'.__('Do you really want to delete this activity?').'"><i class="fa fa-fw fa-trash"></i> '.__('Delete activity').'</a></li>';
		$this->ToolbarLinks[] = '</ul></li>';
	}

	/**
	 * Init sections
	 */
	protected function initSections() {

		if (Configuration::ActivityView()->mapFirst() && Configuration::ActivityView()->plotMode()->showCollection()) {

			$this->Sections[] = new SectionComposite($this->Context);
			$this->Sections[] = new SectionLaps($this->Context);

		} else {

			$this->Sections[] = new SectionOverview($this->Context);

			if (Configuration::ActivityView()->mapFirst()) {
				$this->Sections[] = new SectionRouteOnlyMap($this->Context);
			} else
				$this->Sections[] = new SectionLaps($this->Context);
                                $this->Sections[] = new SectionSwimLane($this->Context);

			if (Configuration::ActivityView()->plotMode()->showSeperated()) {
				$this->Sections[] = new SectionHeartrate($this->Context);
				$this->Sections[] = new SectionPace($this->Context);

				if (Configuration::ActivityView()->mapFirst()) {
					$this->Sections[] = new SectionRouteOnlyElevation($this->Context);
				} else
					$this->Sections[] = new SectionRoute($this->Context);

			} else {
				$this->Sections[] = new SectionComposite($this->Context);

				if (Configuration::ActivityView()->plotMode()->showPaceAndHR()) {

					if (Configuration::ActivityView()->mapFirst()) {
						$this->Sections[] = new SectionRouteOnlyElevation($this->Context);
					} else
						$this->Sections[] = new SectionRoute($this->Context);

				} else {
					if (!Configuration::ActivityView()->mapFirst())
						$this->Sections[] = new SectionRouteOnlyMap($this->Context);
				}
			}

			if (Configuration::ActivityView()->mapFirst()) {
				$this->Sections[] = new SectionLaps($this->Context);
			}
		}

		if (
			(
				$this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::CADENCE) &&
				$this->Context->sport()->getInternalProfileEnum() == \Runalyze\Profile\Sport\SportProfile::RUNNING
			) ||
			$this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::GROUNDCONTACT) ||
			$this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::VERTICAL_OSCILLATION)
		) {
			$this->Sections[] = new SectionRunningDynamics($this->Context);
            $this->Sections[] = new SectionRunScribeData($this->Context);
			$this->Sections[] = new SectionMiscellaneous($this->Context, false);
		} else {
            $this->Sections[] = new SectionRunScribeData($this->Context);
			$this->Sections[] = new SectionMiscellaneous($this->Context, true);
		}

		if ($this->Context->hasHRV()) {
			$this->Sections[] = new SectionHRV($this->Context);
		}
	}

	/**
	 * Display
	 */
	public function display() {
		$this->displayHeader();
		$this->displaySections();
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo '<div class="panel-heading">';

		if (!Request::isOnSharedPage()) {
			$this->displayHeaderMenu();
			$this->displayNavigation();
		} else {
			$this->displaySharedMenu();
		}

		echo '<h1>'.$this->Context->dataview()->titleWithComment().'</h1>';

		if (!Request::isOnSharedPage()) {
			$this->displayReloadLink();
		}

		echo '</div>';
	}

	/**
	 * Display header menu
	 */
	protected function displayHeaderMenu() {
		echo '<div class="panel-menu"><ul>';

		foreach ($this->ToolbarLinks as $link) {
			if (substr($link, 0, 3) != '<li' && substr($link, 0, 2) != '</') {
				$link = '<li>'.$link.'</li>';
			}

			echo $link;
		}

		echo '</ul></div>';
	}

	/**
	 * Display shared menu
	 */
	protected function displaySharedMenu() {
		$User = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `id`="'.(int)SharedLinker::getUserId().'" LIMIT 1')->fetch();

		$this->ToolbarLinks = array();
		$this->ToolbarLinks[] = SharedLinker::getStandardLinkTo( $this->Context->activity()->id(), Icon::$ATTACH );
		$this->ToolbarLinks[] = '<a href="athlete/'.$User['username'].'" target="_blank">'.Icon::$TABLE.'</a>';

		$this->displayHeaderMenu();
	}

	/**
	 * Display prev/next navigation
	 */
	protected function displayNavigation() {
		$prevId = Linker::prevId($this->Context->activity()->id(), $this->Context->activity()->timestamp());
		$nextId = Linker::nextId($this->Context->activity()->id(), $this->Context->activity()->timestamp());

		if ($prevId !== false) {
			echo Ajax::trainingLink($prevId, '<i class="fa fa-fw fa-chevron-left"></i>');
		} else {
			echo '<i class="transparent-70 fa-grey fa fa-fw fa-chevron-left"></i>';
		}

		if ($nextId !== false) {
			echo Ajax::trainingLink($nextId, '<i class="fa fa-fw fa-chevron-right"></i>');
		} else {
			echo '<i class="transparent-70 fa-grey fa fa-fw fa-chevron-right"></i>';
		}
	}

	/**
	 * Display reload link
	 */
	protected function displayReloadLink() {
		echo '<div class="hover-icons"><span class="link" onclick="Runalyze.Statistics.reload();">'.Icon::$REFRESH_SMALL.'</span></div>';
	}

	/**
	 * Display sections
	 */
	protected function displaySections() {
		foreach ($this->Sections as &$Section)
			$Section->display();

		$this->initPlots();
	}

	/**
	 * Init plots
	 */
	protected function initPlots() {
		echo Ajax::wrapJSforDocumentReady( 'RunalyzePlot.resizeTrainingCharts();' );
	}
}
