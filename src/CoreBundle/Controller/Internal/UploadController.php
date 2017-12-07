<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Internal;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/_internal/upload")
 */
class UploadController extends Controller
{
    /**
     * @Route("", name="internal-activity-upload")
     * @Security("has_role('ROLE_USER')")
     */
    public function uploadAction(Request $request)
    {
        if ($request->files->has('qqfile')) {
            /** @var UploadedFile $file */
            $file = $request->files->get('qqfile');
            $newFileName = str_replace(';', '_-_', $file->getClientOriginalName());

            if (class_exists('Normalizer')) {
                $newFileName = \Normalizer::normalize($newFileName);
            }

            try {
                $file->move(
                    $this->getParameter('data_directory').'/import',
                    $newFileName
                );

                return new JsonResponse(['success' => true]);
            } catch (FileException $e) {
                return new JsonResponse(['error' => $e->getMessage()]);
            }
        }

        return new JsonResponse(['error' => 'No file given.']);
    }
}
