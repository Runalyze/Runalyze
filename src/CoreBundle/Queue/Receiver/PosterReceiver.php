<?php

namespace Runalyze\Bundle\CoreBundle\Queue\Receiver;

use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\FileHandler;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\GeneratePoster;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\SvgToPngConverter;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\GenerateJsonData;

class PosterReceiver
{

    /** @var AccountRepository */
    protected $accountRepository;

    /** @var AccountRepository */
    protected $sportRepository;

    /** @var GenerateJsonData */
    protected $generateJsonData;

    /** @var GeneratePoster */
    protected $generatePoster;

    /** @var SvgToPngConverter */
    protected $svgToPng;

    /** @var FileHandler */
    protected $fileHandler;

    /** @var $kernelRootDir */
    protected $kernelRootDir;

    /**
     * PosterReceiver constructor.
     * @param AccountRepository $accountRepository
     * @param SportRepository $sportRepository
     * @param GenerateJsonData $generateJsonData
     * @param GeneratePoster $generatePoster
     * @param SvgToPngConverter $svgToPng
     * @param FileHandler $posterFileHandler
     * @param $kernelRootDir
     */
    public function __construct(AccountRepository $accountRepository, SportRepository $sportRepository, GenerateJsonData $generateJsonData, GeneratePoster $generatePoster, SvgToPngConverter $svgToPng, FileHandler $posterFileHandler, $kernelRootDir)
    {
        $this->accountRepository = $accountRepository;
        $this->sportRepository = $sportRepository;
        $this->generateJsonData = $generateJsonData;
        $this->generatePoster = $generatePoster;
        $this->svgToPng = $svgToPng;
        $this->fileHandler = $posterFileHandler;
        $this->kernelRootDir = $kernelRootDir;
    }

    public function posterGenerator($message) {

        /** @var Account $account */
        $account = $this->accountRepository->find((int)$message->get('accountid'));

        /** @var Sport $sport */
        $sport = $this->sportRepository->find((int)$message->get('sportid'));

        /** @var GenerateJsonData $jsonFiles */
        $jsonFiles = $this->generateJsonData;
        $jsonFiles->createJsonFilesFor($account, $sport, $message->get('year'));

        /** @var GeneratePoster $posterGenerator */
        $posterGenerator = $this->generatePoster;

        /** @var SvgToPngConverter $svgToPng */
        $svgToPng = $this->svgToPng;
        $svgToPng->setHeight($message->get('size'));

        foreach ($message->get('types') as $type) {
            $posterGenerator->buildCommand($type, $jsonFiles->getPathToJsonFiles(), $message->get('year'), $account->getUsername(), $message->get('title'));
            $svg = $posterGenerator->generate();
            $svgToPng->convert($svg, $this->exportDirectory().$this->fileHandler->buildFinalFileName($account, $sport, $message->get('year'), $type, $message->get('size')));
            $posterGenerator->deleteSvg();
        }

        $jsonFiles->deleteGeneratedFiles();


    }

    /**
     * @return string
     */
    private function exportDirectory() {
        return $this->kernelRootDir.'/../data/poster/';
    }

}