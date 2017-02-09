<?php

namespace Runalyze\Bundle\CoreBundle\Queue\Receiver;

use Bernard\Message\DefaultMessage;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\GeneratePoster;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    /**
     * PosterReceiver constructor
     * @param AccountRepository $accountRepository
     * @param SportRepository $sportRepository
     * @param GenerateJsonData $generateJsonData
     * @param GeneratePoster $generatePoster
     */
    public function __construct(AccountRepository $accountRepository, SportRepository $sportRepository, GenerateJsonData $generateJsonData, GeneratePoster $generatePoster)
    {
        $this->accountRepository = $accountRepository;
        $this->sportRepository = $sportRepository;
        $this->generateJsonData = $generateJsonData;
        $this->generatePoster = $generatePoster;
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

        $svgFiles = array();
        foreach ($message->get('types') as $type) {
            $posterGenerator->buildCommand($type, $jsonFiles->getPathToJsonFiles(), $message->get('year'), $account->getUsername());
            $svgFiles[] = $posterGenerator->generate();
        }

    }

}