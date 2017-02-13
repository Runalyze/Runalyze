<?php

namespace Runalyze\Bundle\CoreBundle\Queue\Receiver;

use Bernard\Message\DefaultMessage;
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
    protected $AccountRepository;

    /** @var SportRepository */
    protected $SportRepository;

    /** @var GenerateJsonData */
    protected $GenerateJsonData;

    /** @var GeneratePoster */
    protected $GeneratePoster;

    /** @var SvgToPngConverter */
    protected $SvgToPng;

    /** @var FileHandler */
    protected $FileHandler;

    /** @var $kernelRootDir */
    protected $KernelRootDir;

    /**
     * @param AccountRepository $accountRepository
     * @param SportRepository $sportRepository
     * @param GenerateJsonData $generateJsonData
     * @param GeneratePoster $generatePoster
     * @param SvgToPngConverter $svgToPng
     * @param FileHandler $posterFileHandler
     * @param string $kernelRootDir
     */
    public function __construct(
        AccountRepository $accountRepository,
        SportRepository $sportRepository,
        GenerateJsonData $generateJsonData,
        GeneratePoster $generatePoster,
        SvgToPngConverter $svgToPng,
        FileHandler $posterFileHandler,
        $kernelRootDir
    )
    {
        $this->AccountRepository = $accountRepository;
        $this->SportRepository = $sportRepository;
        $this->GenerateJsonData = $generateJsonData;
        $this->GeneratePoster = $generatePoster;
        $this->SvgToPng = $svgToPng;
        $this->FileHandler = $posterFileHandler;
        $this->KernelRootDir = $kernelRootDir;
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

        $this->GenerateJsonData->createJsonFilesFor($account, $sport, $message->get('year'));
        $this->SvgToPng->setHeight($message->get('size'));

        foreach ($message->get('types') as $type) {
            $this->GeneratePoster->buildCommand($type, $this->GenerateJsonData->getPathToJsonFiles(), $message->get('year'), $account, $sport, $message->get('title'));
            $this->SvgToPng->convert(
                $this->GeneratePoster->generate(),
                $this->exportDirectory().$this->FileHandler->buildFinalFileName($account, $sport, $message->get('year'), $type, $message->get('size'))
            );
            $this->GeneratePoster->deleteSvg();
        }

        $this->GenerateJsonData->deleteGeneratedFiles();
    }

    /**
     * @return string
     */
    protected function exportDirectory()
    {
        return $this->KernelRootDir.'/../data/poster/';
    }
}
