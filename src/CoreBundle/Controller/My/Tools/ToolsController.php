<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My\Tools;

use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\AnovaDataQuery;
use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobGeneral;
use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobLoop;
use Runalyze\Bundle\CoreBundle\Component\Tool\TrendAnalysis\TrendAnalysisDataQuery;
use Runalyze\Bundle\CoreBundle\Component\Tool\VO2maxAnalysis\VO2maxAnalysis;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaType;
use Runalyze\Bundle\CoreBundle\Form\Tools\DatabaseCleanupType;
use Runalyze\Bundle\CoreBundle\Form\Tools\PosterType;
use Runalyze\Bundle\CoreBundle\Form\Tools\TrendAnalysis\TrendAnalysisData;
use Runalyze\Bundle\CoreBundle\Form\Tools\TrendAnalysis\TrendAnalysisType;
use Runalyze\Metrics\Common\JavaScriptFormatter;
use Runalyze\Sports\Running\Prognosis\VO2max;
use Runalyze\Sports\Running\VO2max\VO2maxVelocity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Bernard\Message\DefaultMessage;

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
        $config = $this->get('app.configuration_manager')->getList();
        $running = $this->getDoctrine()->getRepository('CoreBundle:Sport')->find($config->getGeneral()->getRunningSport());

        return $this->render('tools/tables/vo2max_paces.html.twig', [
            'currentVo2max' => $config->getCurrentVO2maxShape(),
            'vo2maxVelocity' => new VO2maxVelocity(),
            'paceUnit' => $config->getUnitSystem()->getPaceUnit($running)
        ]);
    }

    /**
     * @Route("/my/tools/tables/general-pace", name="tools-tables-general-pace")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableGeneralPaceAction()
    {
        return $this->render('tools/tables/general_paces.html.twig');
    }

    /**
     * @Route("/my/tools/tables/vo2max", name="tools-tables-vo2max")
     * @Route("/my/tools/tables", name="tools-tables")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableVo2maxRaceResultAction()
    {
        return $this->render('tools/tables/vo2max.html.twig', [
            'currentVo2max' => $this->get('app.configuration_manager')->getList()->getCurrentVO2maxShape(),
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
        $correctionFactor = $configuration->getVO2maxCorrectionFactor();

        $analysisTable = new VO2maxAnalysis($configuration->getVO2max()->getLegacyCategory());
        $races = $analysisTable->getAnalysisForAllRaces(
            $correctionFactor,
            $configuration->getGeneral()->getRunningSport(),
            $account->getId()
        );

        return $this->render('tools/vo2max_analysis.html.twig', [
            'races' => $races,
            'vo2maxFactor' => $correctionFactor
        ]);
    }

    /**
     * @Route("/my/tools/anova", name="tools-anova")
     * @Security("has_role('ROLE_USER')")
     */
    public function anovaAction(Request $request, Account $account)
    {
        $data = AnovaData::getDefault($this->getDoctrine()->getRepository('CoreBundle:Sport')->findAllFor($account), []);

        $form = $this->createForm(AnovaType::class, $data, [
            'action' => $this->generateUrl('tools-anova')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return new JsonResponse(['status' => 'There was a problem.']);
            }

            $unitSystem = $this->get('app.configuration_manager')->getList($account)->getUnitSystem();
            $query = new AnovaDataQuery($data);
            $query->loadAllGroups($this->getDoctrine()->getManager(), $account);

            return new JsonResponse([
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
     * @Route("/my/tools/trend-analysis", name="tools-trend-analysis")
     * @Security("has_role('ROLE_USER')")
     */
    public function trendAnalysisAction(Request $request, Account $account)
    {
        $data = TrendAnalysisData::getDefault($this->getDoctrine()->getRepository('CoreBundle:Sport')->findAllFor($account), []);

        $form = $this->createForm(TrendAnalysisType::class, $data, [
            'action' => $this->generateUrl('tools-trend-analysis')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                return new JsonResponse(['status' => 'There was a problem.']);
            }

            $unitSystem = $this->get('app.configuration_manager')->getList($account)->getUnitSystem();
            $query = new TrendAnalysisDataQuery($data);

            return new JsonResponse([
                'tickFormatter' => JavaScriptFormatter::getFormatter($query->getValueUnit($unitSystem)),
                'values' => $query->getResults(
                    $this->getDoctrine()->getRepository('CoreBundle:Training'),
                    $account, $unitSystem
                )
            ]);
        }

        return $this->render('tools/trend-analysis/base.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/my/tools/poster", name="poster")
     * @Security("has_role('ROLE_USER')")
     */
    public function posterAction(Request $request, Account $account)
    {
        $numberOfActivities = $this->getDoctrine()->getRepository('CoreBundle:Training')->getNumberOfActivitiesFor($account, (int)2017, (int)2);

        $form = $this->createForm(PosterType::class, [
            'postertype' => ['heatmap'],
            'year' => date('Y') - 1,
            'title' => ' '
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $formdata = $request->request->get($form->getName());

            $numberOfActivities = $this->getDoctrine()->getRepository('CoreBundle:Training')->getNumberOfActivitiesFor($account, (int)$formdata['year'], (int)$formdata['sport']);
            if ($numberOfActivities <= 1) {
                $this->addFlash('error', $this->get('translator')->trans('There are not enough activities to generate a poster. Please change your selection.'));
            } else {
                $message = new DefaultMessage('posterGenerator', array(
                    'accountid' => $account->getId(),
                    'year' => $formdata['year'],
                    'types' => $formdata['postertype'],
                    'sportid' => $formdata['sport'],
                    'title' => $formdata['title'],
                    'size' => $formdata['size']
                ));
                $this->get('bernard.producer')->produce($message);

                return $this->render('tools/poster_success.html.twig', [
                    'posterStoragePeriod' => $this->getParameter('poster_storage_period'),
                    'listing' => $this->get('app.poster.filehandler')->getFileList($account)
                ]);
            }
        }

        return $this->render('tools/poster.html.twig', [
            'form' => $form->createView(),
            'posterStoragePeriod' => $this->getParameter('poster_storage_period'),
            'listing' => $this->get('app.poster.filehandler')->getFileList($account)
        ]);
    }

    /**
     * @Route("/my/tools/poster/{name}", name="poster-download", requirements={"name": ".+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function posterDownloadAction(Account $account, $name)
    {
        return $this->get('app.poster.filehandler')->getPosterDownloadResponse($account, $name);
    }

    /**
     * @Route("/my/tools", name="tools")
     * @Security("has_role('ROLE_USER')")
     */
    public function overviewAction()
    {
        return $this->render('tools/tools_list.html.twig', [
            'posterAvailable' => $this->get('app.poster.availability')->isAvailable()
        ]);
    }
}
