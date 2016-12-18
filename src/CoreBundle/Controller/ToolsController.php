<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Activity\Distance;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\AnovaDataQuery;
use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobGeneral;
use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobLoop;
use Runalyze\Bundle\CoreBundle\Component\Tool\Table\GeneralPaceTable;
use Runalyze\Bundle\CoreBundle\Component\Tool\Table\VdotRaceResultsTable;
use Runalyze\Bundle\CoreBundle\Component\Tool\Table\VdotPaceTable;
use Runalyze\Bundle\CoreBundle\Component\Tool\VdotAnalysis\VdotAnalysis;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaType;
use Runalyze\Configuration;
use Runalyze\Metrics\Common\JavaScriptFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ToolsController extends Controller
{
    /**
     * @Route("/my/tools/cleanup", name="tools-cleanup")
     * @Security("has_role('ROLE_USER')")
     *
     * @TODO use symfony form
     */
    public function cleanupAction(Request $request, Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $mode = $request->request->get('mode');
        $prefix = $this->getParameter('database_prefix');

        if (null !== $mode) {
            if ('general' === $mode) {
                $job = new JobGeneral($request->request->all(), \DB::getInstance(), $account->getId(), $prefix);
            } else {
                $job = new JobLoop($request->request->all(), \DB::getInstance(), $account->getId(), $prefix);
            }

            $job->run();

            return $this->render('tools/database_cleanup/results.html.twig', [
                'messages' => $job->messages()
            ]);
        }

        return $this->render('tools/database_cleanup/form.html.twig');
    }

    /**
     * @Route("/my/tools/tables/vdot-pace", name="tools-tables-vdot-pace")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableVdotPaceAction()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        return $this->render('tools/tables/vdot_paces.html.twig', [
            'currentVdot' => Configuration::Data()->vdot(),
            'vdots' => (new VdotPaceTable())->getVdotPaces(range(30, 80))
        ]);
    }

    /**
     * @Route("/my/tools/tables/general-pace", name="tools-tables-general-pace")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableGeneralPaceAction()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $distances = [0.2, 0.4, 1, 3, 5, 10, 21.1, 42.2, 50];

        return $this->render('tools/tables/general_paces.html.twig', [
            'distances' => array_map(function($km) { return (new Distance($km))->stringAuto(); }, $distances),
            'paces' => (new GeneralPaceTable())->getPaces($distances, range(60, 180))
        ]);
    }

    /**
     * @Route("/my/tools/tables/vdot", name="tools-tables-vdot")
     * @Route("/my/tools/tables", name="tools-tables")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableVdotRaceResultAction()
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $distances = [1, 3, 5, 10, 21.1, 42.2, 50];

        return $this->render('tools/tables/vdot.html.twig', [
            'currentVdot' => Configuration::Data()->vdot(),
            'distances' => array_map(function($km) { return (new Distance($km))->stringAuto(); }, $distances),
            'vdots' => (new VdotRaceResultsTable())->getVdotRaceResults($distances, range(30, 80))
        ]);
    }

    /**
     * @Route("/my/tools/vdot-analysis", name="tools-vdot-analysis")
     * @Security("has_role('ROLE_USER')")
     */
    public function vdotAnalysisAction(Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $configuration = $this->get('app.configuration_manager')->getList();
        $vdotFactor = $configuration->getVdotFactor();

        $analysisTable = new VdotAnalysis($configuration->getVdot()->getLegacyCategory());
        $races = $analysisTable->getAnalysisForAllRaces(
            $vdotFactor,
            $configuration->getGeneral()->getRunningSport(),
            $account->getId()
        );

        return $this->render('tools/vdot_analysis.html.twig', [
            'races' => $races,
            'vdotFactor' => $vdotFactor
        ]);
    }

    /**
     * @Route("/my/tools/anova", name="tools-anova")
     * @Security("has_role('ROLE_USER')")
     */
    public function anovaAction(Request $request, Account $account)
    {
        // This should go to AnovaType ... but how?
        $data = new AnovaData();
        $data->setSport($this->getDoctrine()->getRepository('CoreBundle:Sport')->findAllFor($account));
        $data->setDateFrom((new \DateTime())->sub(new \DateInterval('P6M')));
        $data->setDateTo(new \DateTime());

        $form = $this->createForm(AnovaType::class, $data, [
            'action' => $this->generateUrl('tools-anova')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return $this->json([
                    'status' => 'There was a problem.'
                ]);
            }

            $unitSystem = $this->get('app.configuration_manager')->getList($account)->getUnitSystem();
            $query = new AnovaDataQuery($data);
            $query->loadAllGroups($this->getDoctrine()->getManager(), $account);

            return $this->json([
                'tickFormatter' => JavaScriptFormatter::getFormatter($query->getValueUnit($unitSystem)),
                'groups' => $query->getResults(
                    $this->getDoctrine()->getRepository('CoreBundle:Training'),
                    $account, $unitSystem
                )
            ]);
        }

        return $this->render('tools/anova/base.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/my/tools", name="tools")
     * @Security("has_role('ROLE_USER')")
     */
    public function overviewAction()
    {
        return $this->render('tools/tools_list.html.twig');
    }
}
