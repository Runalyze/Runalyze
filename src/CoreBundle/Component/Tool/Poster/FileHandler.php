<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Symfony\Component\Filesystem\Filesystem;
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
        /** @var Finder $finder */
        $finder = new Finder();
        $finder->files()->name($account->getId().'-*')
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
                return ($b->getMTime() - $a->getMTime());
            })
            ->in($this->pathToImages());


        $list = array();
        foreach($finder as $file) {
            $list[str_replace($account->getId().'-', '', $file->getBasename())] = $file->getSize();
        }
        return $list;
    }

    /**
     * @param Account $account
     * @param $filename
     * @return Response
     */
    public function getPosterDownloadResponse(Account $account, $filename) {
        $fs = new Filesystem();
        $filename = $account->getId().'-'.$filename;
        if ($fs->exists($this->pathToImages().$filename))  {
            $response = new Response();
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', 'image/png');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($filename) . '";');
            $response->headers->set('Content-length', filesize($this->pathToImages() . $filename));
            $response->setContent(file_get_contents($this->pathToImages() . $filename));
            return $response;
        }
    }

    /**
     * @param Account $account
     * @param Sport $sport
     * @param $year
     * @param $type
     * @param $size
     * @return string
     */
    public function buildFinalFileName(Account $account, Sport $sport, $year, $type, $size) {
        return sprintf('%s-%s-%s-%s-%s-%s.%s',
            $account->getId(),
            $this->filesystemFriendlyName($sport->getName()),
            $year,
            $type,
            $size,
            date('Ymd-Hi'),
            'png'
        );
    }

    /**
     * @param $string
     * @return string
     */
    private function filesystemFriendlyName($string) {
        return preg_replace('~[^a-zA-Z0-9]+~', '', $string);;
    }

}