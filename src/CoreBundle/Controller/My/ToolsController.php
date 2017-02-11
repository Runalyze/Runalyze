<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My;

use Runalyze\Activity\Distance;
use Runalyze\Bundle\CoreBundle\Component\Tool\Anova\AnovaDataQuery;
use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobGeneral;
use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\JobLoop;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\FileHandler;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Listing;
use Runalyze\Bundle\CoreBundle\Component\Tool\Table\GeneralPaceTable;
use Runalyze\Bundle\CoreBundle\Component\Tool\Table\VdotRaceResultsTable;
use Runalyze\Bundle\CoreBundle\Component\Tool\Table\VdotPaceTable;
use Runalyze\Bundle\CoreBundle\Component\Tool\VdotAnalysis\VdotAnalysis;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\DatabaseCleanupType;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaData;
use Runalyze\Bundle\CoreBundle\Form\Tools\Anova\AnovaType;
use Runalyze\Bundle\CoreBundle\Form\Tools\PosterType;
use Runalyze\Configuration;
use Runalyze\Metrics\Common\JavaScriptFormatter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Bernard\Message\DefaultMessage;

class ToolsController extends Controller
{
    /**
     * @Route("/my/tools/cleanup", name="tools-cleanup")
     * @Security("has_role('ROLE_USER')")
     *
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
     * @Route("/my/tools/tables/vdot-pace", name="tools-tables-vdot-pace")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableVdotPaceAction()
    {
        return $this->render('tools/tables/vdot_paces.html.twig', [
            'currentVdot' => $this->get('app.configuration_manager')->getList()->getCurrentVdot(),
            'vdots' => (new VdotPaceTable())->getVdotPaces(range(30, 80))
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
     * @Route("/my/tools/tables/vdot", name="tools-tables-vdot")
     * @Route("/my/tools/tables", name="tools-tables")
     * @Security("has_role('ROLE_USER')")
     */
    public function tableVdotRaceResultAction()
    {
        $distances = [1, 3, 5, 10, 21.1, 42.2, 50];
        return $this->render('tools/tables/vdot.html.twig', [
            'currentVdot' => $this->get('app.configuration_manager')->getList()->getCurrentVdot(),
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
     * @Route("/my/tools/poster", name="poster")
     * @Security("has_role('ROLE_USER')")
     */
    public function posterAction(Request $request, Account $account)
    {
        $form = $this->createForm(PosterType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formdata = $request->request->get($form->getName());
            $message = new DefaultMessage('posterGenerator', array(
                'accountid' => $account->getId(),
                'year' => $formdata['year'],
                'types' => $formdata['postertype'],
                'sportid' => $formdata[ 'sport'],
                'title' => $formdata[ 'title']
            ));
            $this->get('bernard.producer')->produce($message);
        }

        /** @var Listing $posterListing */
        $posterListing = $this->get('app.poster.filehandler');
        return $this->render('tools/poster.html.twig', [
            'form' => $form->createView(),
            'posterStoragePeriod' => $this->getParameter('poster_storage_period'),
            'listing' => $posterListing->getFileList($account)
        ]);
    }

    /**
     * @Route("/my/tools/poster/{name}", name="poster-download")
     * @Security("has_role('ROLE_USER')")
     */
    public function posterDownloadAction(Account $account, $name)
    {
            /** @var FileHandler */
            return $this->get('app.poster.filehandler')->getPosterDownloadResponse($account, $name);
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
