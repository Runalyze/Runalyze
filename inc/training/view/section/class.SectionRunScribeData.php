<?php

use Runalyze\Model\Trackdata;

class SectionRunScribeData extends TrainingViewSectionTabbedPlot
{
	protected function setHeaderAndRows()
    {
		$this->Header = 'RunScribe <a target="_blank" href="https://runscribe.com/metrics/"><i class="fa fa-question-circle-o"></i></a>';

		$this->appendRowTabbedPlot(new SectionRunScribeDataRow($this->Context));
	}

	protected function hasRequiredData()
    {
		return $this->Context->hasTrackdata() && $this->Context->trackdata()->hasRunScribeData();
	}

	protected function cssId()
    {
		return 'runscribe';
	}
}
