<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\FilenameHandler;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\JsonBackupAnalyzer;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\JsonImporter;
use Runalyze\Calculation\Prognosis;
use Runalyze\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("/my/tools/backup-import")
 */
class JsonImportToolController extends Controller
{
    /**
     * @return string
     */
    protected function getImportFilePath()
    {
        return $this->getParameter('kernel.root_dir').'/../data/backup-tool/import/';
    }

    /**
     * @Route("/upload", name="tools-backup-json-upload")
     * @Security("has_role('ROLE_USER')")
     *
     * @TODO use symfony to move uploaded file
     */
    public function backupUploadAction()
    {
        if (!FilenameHandler::validateImportFileExtension($_FILES['qqfile']['name'])) {
            return $this->json(['error' => 'Wrong file extension.']);
        }

        if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $this->getImportFilePath().$_FILES['qqfile']['name'])) {
            return $this->json(['error' => 'Moving file did not work. Set chmod 777 for /data/backup-tool/import/']);
        }

        $this->get('session')->getFlashBag()->set('json-import.file', $_FILES['qqfile']['name']);

        return $this->json(['success' => true]);
    }

    /**
     * @Route("/import", name="tools-backup-json-import")
     * @Security("has_role('ROLE_USER')")
     */
    public function backupImportAction()
    {
        $sessionFlashBag = $this->get('session')->getFlashBag();

        if (!$sessionFlashBag->has('json-import.file')) {
            return $this->redirectToRoute('tools-backup-json');
        }

        $filePath = $this->getImportFilePath();
        $filename = $sessionFlashBag->get('json-import.file')[0];
        $fileInfo = new \SplFileInfo($filePath.$filename);
        $analyzer = new JsonBackupAnalyzer($filePath.$filename, $this->getParameter('runalyze_version'));

        if (!$analyzer->fileIsOkay()) {
            (new Filesystem())->remove($filePath.$filename);

            return $this->render('tools/backup/import_bad_file.html.twig', [
                'file' => $fileInfo,
                'versionIsOkay' => $analyzer->versionIsOkay(),
                'runalyzeVersion' => $this->getParameter('runalyze_version'),
                'runalyzeVersionFile' => $analyzer->fileVersion()
            ]);
        }

        $sessionFlashBag->set('json-import.file', $filename);

        return $this->render('tools/backup/import_form.html.twig', [
            'file' => $fileInfo,
            'numActivities' => $analyzer->count('runalyze_training'),
            'numBodyValues' => $analyzer->count('runalyze_user')
        ]);
    }

    /**
     * @Route("/import/do", name="tools-backup-json-import-do")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Request $request
     * @return Response
     */
    public function backupImportDoAction(Request $request)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $sessionFlashBag = $this->get('session')->getFlashBag();

        if (!$sessionFlashBag->has('json-import.file')) {
            return $this->redirectToRoute('tools-backup-json');
        }

        $filePath = $this->getImportFilePath();
        $filename = $sessionFlashBag->get('json-import.file')[0];

        $importer = new JsonImporter(
            $filePath.$filename,
            \DB::getInstance(),
            $this->get('security.token_storage')->getToken()->getUser()->getId(),
            $this->getParameter('database_prefix')
        );

        if ($request->request->get('delete_trainings')) {
            $importer->deleteOldActivities();
        }

        if ($request->request->get('delete_user_data')) {
            $importer->deleteOldBodyValues();
        }

        $importer->enableOverwritingConfig($request->request->get('overwrite_config'));
        $importer->enableOverwritingDataset($request->request->get('overwrite_dataset'));
        $importer->enableOverwritingPlugins($request->request->get('overwrite_plugin'));
        $importer->importData();

        return $this->render('tools/backup/import_finish.html.twig', [
            'results' => $importer->resultsAsString()
        ]);
    }

    /**
     * @Route("", name="tools-backup-json")
     * @Security("has_role('ROLE_USER')")
     */
    public function uploadFormAction()
    {
        return $this->render('tools/backup/upload_form.html.twig', [
            'runalyzeVersion' => $this->getParameter('runalyze_version')
        ]);
    }
}
