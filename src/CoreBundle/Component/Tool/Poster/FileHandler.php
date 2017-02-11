<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

class FileHandler
{
    protected $kernelRootDir;

    /**
     * Listing constructor
     * @param $kernelRootDir
     */
    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    protected function pathToImages() {
        return $this->kernelRootDir.'/../data/poster/';
    }

    public function getFileList(Account $account) {
        $finder = new Finder();
        $finder->files()->name($account->getId().'-*')->in($this->pathToImages());
        return $finder;
    }

    /**
     * @param Account $account
     * @param $filename
     * @return Response
     */
    public function getPosterDownloadResponse(Account $account, $filename) {
        if (strpos($filename, $account->getId().'-') !== false) {
            $response = new Response();
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', 'image/png');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
            $response->headers->set('Content-length', filesize($this->pathToImages() . $filename));
            $response->setContent(file_get_contents($this->pathToImages() . $filename));
            return $response;
        }
    }
}