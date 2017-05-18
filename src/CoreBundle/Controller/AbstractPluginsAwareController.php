<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Component\Statistics\MonthlyStats\AnalysisData;
use Runalyze\Bundle\CoreBundle\Component\Statistics\MonthlyStats\AnalysisSelection;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Twig\ValueExtension;
use Runalyze\Util\LocalTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPluginsAwareController extends Controller
{
    protected $IsShowingAllPanels = false;

    /**
     * @param Request $request
     * @param Account $account
     * @return Response
     */
    protected function getResponseForAllEnabledPanels(Request $request, Account $account)
    {
        $this->IsShowingAllPanels = true;
        $factory = new \PluginFactory();
        $content = '';

        foreach ($factory->enabledPanels() as $key) {
            $panel = $factory->newInstance($key);
            $panelContent = $this->getResponseFor($panel->id(), $request, $account)->getContent();

            if ($panel instanceof \RunalyzePluginPanel_Sports) {
                $panelContent = '<div class="panel" id="panel-'.$panel->id().'">'.$panelContent.'</div>';
            }

            $content .= $panelContent;
        }

        return new Response($content);
    }

    /**
     * @param int $pluginId
     * @param Request $request
     * @param Account $account
     * @return Response
     */
    protected function getResponseFor($pluginId, Request $request, Account $account)
    {
        $factory = new \PluginFactory();
        $content = '';

        try {
        	$plugin = $factory->newInstanceFor($pluginId);
        } catch (\Exception $E) {
            $plugin = null;

        	echo \HTML::error(__('The plugin could not be found.'));
        }

        if (null !== $plugin) {
        	if ($plugin instanceof \RunalyzePluginPanel_Sports) {
        	    return $this->getResponseForSportsPanel($account, $plugin);
        	} elseif ($plugin instanceof \RunalyzePluginStat_MonthlyStats) {
        	    return $this->getResponseForMonthlyStats($request, $account, $pluginId);
            } elseif ($plugin instanceof \PluginPanel) {
                $plugin->setSurroundingDivVisible($this->IsShowingAllPanels);
            }

            ob_start();
            $plugin->display();
        	$content = ob_get_clean();
        }

        return (new Response())->setContent($content);
    }

    /**
     * @param Account $account
     * @param int $pluginId
     * @return Response
     */
    protected function getResponseForSportsPanel(Account $account, \Plugin $plugin)
    {
        $sportRepository = $this->getDoctrine()->getRepository('CoreBundle:Sport');
        $today = (new LocalTime())->setTime(0, 0, 0);

        return $this->render('my/panels/sports/base.html.twig', [
            'isHidden' => $plugin->isHidden(),
            'pluginId' => $plugin->id(),
            'weekStatistics' => $sportRepository->getSportStatisticsSince($today->weekstart(), $account),
            'monthStatistics' => $sportRepository->getSportStatisticsSince($today->setDate($today->format('Y'), $today->format('m'), 1)->getTimestamp(), $account),
            'yearStatistics' => $sportRepository->getSportStatisticsSince($today->setDate($today->format('Y'), 1, 1)->getTimestamp(), $account),
            'totalStatistics' => $sportRepository->getSportStatisticsSince(null, $account)
        ]);
    }

    /**
     * @param Request $request
     * @param Account $account
     * @param int $pluginId
     * @return Response
     */
    protected function getResponseForMonthlyStats(Request $request, Account $account, $pluginId)
    {
        $valueExtension = new ValueExtension($this->get('app.configuration_manager'));
        $sportSelection = $this->get('app.sport_selection_factory')->getSelection($request->get('sport'));
        $analysisList = new AnalysisSelection($request->get('dat'));

        if (!$analysisList->hasCurrentKey()) {
            $analysisList->setCurrentKey(AnalysisSelection::DISTANCE);
        }

        $analysisData = new AnalysisData(
            $sportSelection,
            $analysisList,
            $this->get('doctrine')->getRepository('CoreBundle:Training'),
            $account
        );
        $analysisData->setDefaultValue($valueExtension);

        $unitSystem = $this->get('app.configuration_manager')->getList()->getUnitSystem();

        return $this->render('my/statistics/monthly-stats/base.html.twig', [
            'unitSystem' => $unitSystem,
            'pluginId' => $pluginId,
            'analysisData' => $analysisData
        ]);
    }
}
