<?php

use Runalyze\Model\Trackdata;

class SectionRunScribeData extends TrainingViewSectionTabbedPlot
{
	protected function setHeaderAndRows()
    {
		$this->Header = 'RunScribe';

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
