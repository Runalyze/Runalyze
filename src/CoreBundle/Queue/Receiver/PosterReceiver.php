<?php

namespace Runalyze\Bundle\CoreBundle\Queue\Receiver;

use Bernard\Message\DefaultMessage;
use Psr\Log\LoggerInterface;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter\AbstractSvgToPngConverter;
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
    /** @var LoggerInterface */
    protected $Logger;

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
     * @param LoggerInterface $logger
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
        LoggerInterface $logger,
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
        $this->Logger = $logger;
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
        /*$this->GenerateJsonData->createJsonFilesFor($account, $sport, $message->get('year'));
        $jsonFiles = (new Finder())->files()->in($this->GenerateJsonData->getPathToJsonFiles());

        if ($jsonFiles->count() > 0) {
            foreach ($message->get('types') as $type) {
                try {
                    $this->GeneratePoster->buildCommand($type, $this->GenerateJsonData->getPathToJsonFiles(), $message->get('year'), $account, $sport, $message->get('title'));

                    $finalName = $this->exportDirectory() . $this->FileHandler->buildFinalFileName($account, $sport, $message->get('year'), $type, $message->get('size'));
                    $converter = $this->getConverter($type);
                    $converter->setHeight($message->get('size'));
                    $exitCode = $converter->callConverter($this->GeneratePoster->generate(), $finalName);

                    if ($exitCode > 0) {
                        $this->Logger->error('Poster converter failed', ['type' => $type, 'exitCode' => $exitCode]);
                    } elseif ((new Filesystem())->exists($finalName)) {
                        $generatedFiles++;
                    }

                    $this->GeneratePoster->deleteSvg();
                } catch (\Exception $e) {
                    $this->Logger->error('Poster creation failed', ['type' => $type, 'exception' => $e]);
                }
            }
        }*/

        $this->AccountMailer->sendPosterReadyTo($account,
            (($generatedFiles == count($message->get('types'))) ? true : $generatedFiles));
        $this->GenerateJsonData->deleteGeneratedFiles();
    }

    /**
     * @param string $posterType
     * @return AbstractSvgToPngConverter
     */
    protected function getConverter($posterType)
    {
        if ('circular' == $posterType) {
            return new InkscapeConverter($this->InkscapePath);
        }

        return new RsvgConverter($this->RsvgPath);
    }

    /**
     * @return string
     */
    protected function exportDirectory()
    {
        return $this->KernelRootDir.'/../data/poster/';
    }
}
