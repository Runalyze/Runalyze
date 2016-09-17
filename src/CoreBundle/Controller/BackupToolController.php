<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\FilenameHandler;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\JsonBackup;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\SqlBackup;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Calculation\Prognosis;
use Runalyze\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/my/tools/backup")
 */
class BackupToolController extends Controller
{
    /**
     * @return string
     */
    protected function getPathToBackupFiles()
    {
        return $this->getParameter('kernel.root_dir').'/../data/backup-tool/backup/';
    }

    /**
     * @Route("/download/{filename}", name="tools-backup-download", requirements={"filename": ".+"})
     * @Security("has_role('ROLE_USER')")
     *
     * @param string $filename
     * @param Account $account
     * @return BinaryFileResponse
     */
    public function downloadBackupAction($filename, Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $fileSystem = new Filesystem();
        $fileHandler = new FilenameHandler($account->getId());
        $filePath = $this->getPathToBackupFiles();
        $internalFilename = $fileHandler->transformPublicToInternalFilename($filename);

        if (!$fileSystem->exists($filePath.$internalFilename)) {
            throw $this->createNotFoundException();
        }

        if (!$fileHandler->validateInternalFilename($internalFilename)) {
            throw $this->createAccessDeniedException();
        }

        $response = new BinaryFileResponse($filePath.$internalFilename);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            iconv('UTF-8', 'ASCII//TRANSLIT', $filename)
        );

        return $response;
    }

    /**
     * @Route("/create", name="tools-backup-create")
     * @Security("has_role('ROLE_USER')")
     * @Method("POST")
     *
     * @param Request $request
     * @param Account $account
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @TODO use symfony form and csrf token
     */
    public function backupCreateAction(Request $request, Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $fileHandler = new FilenameHandler($account->getId());
        $fileHandler->setRunalyzeVersion($this->getParameter('runalyze_version'));

        if ($request->request->get('export-type') == 'json') {
            $Backup = new JsonBackup(
                $this->getPathToBackupFiles().$fileHandler->generateInternalFilename(FilenameHandler::JSON_FORMAT),
                \SessionAccountHandler::getId(),
                \DB::getInstance(),
                $this->getParameter('database_prefix'),
                $this->getParameter('runalyze_version')
            );
            $Backup->run();
        } else {
            $Backup = new SqlBackup(
                $this->getPathToBackupFiles().$fileHandler->generateInternalFilename(FilenameHandler::SQL_FORMAT),
                \SessionAccountHandler::getId(),
                \DB::getInstance(),
                $this->getParameter('database_prefix'),
                $this->getParameter('runalyze_version')
            );
            $Backup->run();
        }

        $this->get('session')->getFlashBag()->set('runalyze.backup.created', true);

        return $this->redirectToRoute('tools-backup');
    }

    /**
     * @Route("", name="tools-backup")
     * @Security("has_role('ROLE_USER')")
     *
     * @param Account $account
     * @return Response
     */
    public function backupAction(Account $account)
    {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        $fileHandler = new FilenameHandler($account->getId());
        $finder = new Finder();
        $finder
            ->files()
            ->in($this->getPathToBackupFiles())
            ->filter(function(\SplFileInfo $file) use ($fileHandler) {
                return $fileHandler->validateInternalFilename($file->getFilename());
            })
        ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
            return ($b->getMTime() - $a->getMTime());
        });

        return $this->render('tools/backup/export.html.twig', [
            'backupWasCreated' => $this->get('session')->getFlashBag()->get('runalyze.backup.created'),
            'hasFiles' => $finder->count() > 0,
            'files' => $finder->getIterator()
        ]);
    }
}
