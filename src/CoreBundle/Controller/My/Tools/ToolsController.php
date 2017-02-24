<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My\Tools;

use Runalyze\Activity\Distance;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\AnovaDataQuery;
use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobGeneral;
use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobLoop;
use Runalyze\Bundle\CoreBundle\Component\Tool\Table\GeneralPaceTable;
use Runalyze\Bundle\CoreBundle\Component\Tool\Table\EffectiveVO2maxPaceTable;
use Runalyze\Bundle\CoreBundle\Component\Tool\VO2maxAnalysis\VO2maxAnalysis;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\DatabaseCleanupType;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaType;
use Runalyze\Metrics\Common\JavaScriptFormatter;
use Runalyze\Sports\Running\Prognosis\VO2max;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ToolsController extends Controller
{
    /**
     * @Route("/my/tools/cleanup", name="tools-cleanup")
     * @Security("has_role('ROLE_USER')")
     */
    public function cleanupAction(Request $request, Account $account)
    {
        $prefix = $this->getParameter('database_prefix');

        $defaultData = array();
        $form = $this->createForm(DatabaseCleanupType::class, $defaultData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getData()['mode']) {
            $Frontend = new \Frontend(true, $this->get('security.token_storage'));

            if ('general' === $form->getData()['mode']) {
                $job = new JobGeneral($form->getData(), \DB::getInstance(), $account->getId(), $prefix);
            } else {
                $job = new JobLoop($form->getData(), \DB::getInstance(), $account->getId(), $prefix);
            }

            $job->run();

            return $this->render('tools/database_cleanup/results.html.twig', [
                'messages' => $job->messages()
            ]);
        }

        return $this->render('tools/database_cleanup/form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/my/tools/tables/vo2max-pace", name="tools-tables-vo2max-pace")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableVo2maxPaceAction()
    {
        return $this->render('tools/tables/vo2max_paces.html.twig', [
            'currentVo2max' => $this->get('app.configuration_manager')->getList()->getCurrentVdot(),
            'vo2maxValues' => (new EffectiveVO2maxPaceTable())->getVO2maxPaces(range(30, 80))
        ]);
    }

    /**
     * @Route("/my/tools/tables/general-pace", name="tools-tables-general-pace")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableGeneralPaceAction()
    {
        $distances = [0.2, 0.4, 1, 3, 5, 10, 21.1, 42.2, 50];

        return $this->render('tools/tables/general_paces.html.twig', [
            'distances' => array_map(function($km) { return (new Distance($km))->stringAuto(); }, $distances),
            'paces' => (new GeneralPaceTable())->getPaces($distances, range(60, 180))
        ]);
    }

    /**
     * @Route("/my/tools/tables/vo2max", name="tools-tables-vo2max")
     * @Route("/my/tools/tables", name="tools-tables")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableVo2maxRaceResultAction()
    {
        return $this->render('tools/tables/vo2max.html.twig', [
            'currentVo2max' => $this->get('app.configuration_manager')->getList()->getCurrentVdot(),
            'prognosis' => new VO2max(),
            'distances' => [1.0, 3.0, 5.0, 10.0, 21.1, 42.2, 50],
            'vo2maxValues' => range(30.0, 80.0)
        ]);
    }

    /**
     * @Route("/my/tools/vo2max-analysis", name="tools-vo2max-analysis")
     * @Security("has_role('ROLE_USER')")
     */
    public function vo2maxAnalysisAction(Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $configuration = $this->get('app.configuration_manager')->getList();
        $vdotFactor = $configuration->getVdotFactor();

        $analysisTable = new VO2maxAnalysis($configuration->getVdot()->getLegacyCategory());
        $races = $analysisTable->getAnalysisForAllRaces(
            $vdotFactor,
            $configuration->getGeneral()->getRunningSport(),
            $account->getId()
        );

        return $this->render('tools/vo2max_analysis.html.twig', [
            'races' => $races,
            'vo2maxFactor' => $vdotFactor
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
