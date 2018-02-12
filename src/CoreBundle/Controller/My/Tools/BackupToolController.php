<?php

namespace Runalyze\Bundle\CoreBundle\Controller\My\Tools;

use Bernard\Message\DefaultMessage;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\FilenameHandler;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Form\Tools\BackupExportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
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
        return $this->getParameter('data_directory').'/backup-tool/backup/';
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
     * @Route("", name="tools-backup")
     * @Security("has_role('ROLE_USER')")
     */
    public function backupAction(Account $account, Request $request)
    {
        $lockedRoutes = $this->getDoctrine()->getManager()->getRepository('CoreBundle:Route')->accountHasLockedRoutes($account);
        $hasLockedTrainings = $this->getDoctrine()->getManager()->getRepository('CoreBundle:Training')->accountHasLockedTrainings($account);

        $form = $this->createForm(BackupExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formdata = $request->request->get($form->getName());
            $this->get('bernard.producer')->produce(new DefaultMessage('userBackup', [
                'accountid' => $account->getId(),
                'export-type' => $formdata['fileFormat']
            ]));
            $this->get('session')->getFlashBag()->set('runalyze.backupjob.created', true);
        }

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
            'backupjobWasCreated' => $this->get('session')->getFlashBag()->get('runalyze.backupjob.created'),
            'hasFiles' => $finder->count() > 0,
            'files' => $finder->getIterator(),
            'hasLocks' => ($lockedRoutes || $hasLockedTrainings),
            'form' => $form->createView()
        ]);
    }
}
