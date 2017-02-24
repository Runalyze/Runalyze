<?php

namespace Runalyze\Bundle\CoreBundle\Queue\Receiver;

use Bernard\Message\DefaultMessage;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter\InkscapeConverter;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter\RsvgConverter;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\FileHandler;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\GeneratePoster;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\GenerateJsonData;
use Runalyze\Bundle\CoreBundle\Services\AccountMailer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class PosterReceiver
{
    /** @var AccountRepository */
    protected $AccountRepository;

    /** @var SportRepository */
    protected $SportRepository;

    /** @var GenerateJsonData */
    protected $GenerateJsonData;

    /** @var GeneratePoster */
    protected $GeneratePoster;

    /** @var FileHandler */
    protected $FileHandler;

    /** @var AccountMailer */
    protected $AccountMailer;

    /** @var $kernelRootDir */
    protected $KernelRootDir;

    /** @var $RsvgPath */
    protected $RsvgPath;

    /** @var $InkscapePath */
    protected $InkscapePath;

    /**
     * @param AccountRepository $accountRepository
     * @param SportRepository $sportRepository
     * @param GenerateJsonData $generateJsonData
     * @param GeneratePoster $generatePoster
     * @param FileHandler $posterFileHandler
     * @param AccountMailer $accountMailer
     * @param string $kernelRootDir
     * @param string $rsvgPath
     * @param string $inkscapePath
     */
    public function __construct(
        AccountRepository $accountRepository,
        SportRepository $sportRepository,
        GenerateJsonData $generateJsonData,
        GeneratePoster $generatePoster,
        FileHandler $posterFileHandler,
        AccountMailer $accountMailer,
        $kernelRootDir,
        $rsvgPath,
        $inkscapePath
    )
    {
        $this->AccountRepository = $accountRepository;
        $this->SportRepository = $sportRepository;
        $this->GenerateJsonData = $generateJsonData;
        $this->GeneratePoster = $generatePoster;
        $this->FileHandler = $posterFileHandler;
        $this->AccountMailer = $accountMailer;
        $this->KernelRootDir = $kernelRootDir;
        $this->RsvgPath = $rsvgPath;
        $this->InkscapePath = $inkscapePath;
    }

    public function posterGenerator(DefaultMessage $message)
    {
        /** @var Account|null $account */
        $account = $this->AccountRepository->find((int)$message->get('accountid'));

        /** @var Sport|null $sport */
        $sport = $this->SportRepository->find((int)$message->get('sportid'));

        if (null === $account || null === $sport || $sport->getAccount()->getId() != $account->getId()) {
            return;
        }
        $generatedFiles = 0;
        $this->GenerateJsonData->createJsonFilesFor($account, $sport, $message->get('year'));
        $jsonFiles = (new Finder())->files()->in($this->GenerateJsonData->getPathToJsonFiles());
        if ($jsonFiles > 0) {
            foreach ($message->get('types') as $type) {
                $this->GeneratePoster->buildCommand($type, $this->GenerateJsonData->getPathToJsonFiles(), $message->get('year'), $account, $sport, $message->get('title'));
                $converter = $this->getConverter($type);
                $converter->setHeight($message->get('size'));
                $converter->callConverter(
                    $this->GeneratePoster->generate(),
                    $finalName = $this->exportDirectory() . $this->FileHandler->buildFinalFileName($account, $sport, $message->get('year'), $type, $message->get('size'))
                );
                $this->GeneratePoster->deleteSvg();
                if ((new Filesystem())->exists($finalName)) {
                    $generatedFiles++;
                }
            }
        }
        $this->AccountMailer->sendPosterReadyTo($account,
            (($generatedFiles == count($message->get('types'))) ? true : $generatedFiles));
        $this->GenerateJsonData->deleteGeneratedFiles();
    }

    protected function getConverter($posterType)
    {
        if ($posterType == 'circular') {
            return new InkscapeConverter($this->InkscapePath);
        } else {
            return new RsvgConverter($this->RsvgPath);
        }
    }

    /**
     * @return string
     */
    protected function exportDirectory()
    {
        return $this->KernelRootDir.'/../data/poster/';
    }
}
