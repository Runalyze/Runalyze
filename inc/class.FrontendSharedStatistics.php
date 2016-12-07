<?php

class FrontendSharedStatistics
{
	/**
	 * @return string
	 */
	public function getTabForComparisonOfYears()
    {
		$Content = '';
		$Factory = new PluginFactory();

		if ($Factory->isInstalled('RunalyzePluginStat_Wettkampf')) {
			/** @var RunalyzePluginStat_Wettkampf $Plugin */
			$Plugin = $Factory->newInstance('RunalyzePluginStat_Wettkampf');
			$Content .= ($Plugin->getYearComparisonTable());
		}

		if ($Content == '') {
			$Content = '<em>'.__('No data available.').'</em>';
		}

		return $Content;
	}

    /**
     * @return SummaryTableAllYears
     */
	public function getYearComparisonTable()
    {
        require_once FRONTEND_PATH.'../plugin/RunalyzePluginStat_Statistiken/class.SummaryTable.php';
        require_once FRONTEND_PATH.'../plugin/RunalyzePluginStat_Statistiken/class.SummaryTableAllYears.php';

        return new SummaryTableAllYears(
            new \Runalyze\Dataset\Configuration(DB::getInstance(), SessionAccountHandler::getId()),
            \Runalyze\Configuration::General()->runningSport(),
            -1
        );
    }
}
