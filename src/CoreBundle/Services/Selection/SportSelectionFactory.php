<?php

namespace Runalyze\Bundle\CoreBundle\Services\Selection;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Services\TokenStorageAwareServiceTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SportSelectionFactory
{
    use TokenStorageAwareServiceTrait;

    /** @var string */
    const ALL = 'all';

    /** @var SportRepository */
    protected $SportRepository;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    public function __construct(
        SportRepository $sportRepository,
        ConfigurationManager $configurationManager,
        TokenStorage $tokenStorage
    )
    {
        $this->SportRepository = $sportRepository;
        $this->ConfigurationManager = $configurationManager;
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @param string|null $param
     * @param bool $allIsAvailable
     * @return Selection
     */
    public function getSelection($param = null, $allIsAvailable = true)
    {
        if (!$this->knowsUser()) {
            throw new \RuntimeException('Sport selection can only be collected if the user is known.');
        }

        $sportList = [];

        if ($allIsAvailable) {
            $sportList[self::ALL] = 'All';
        }

        foreach ($this->SportRepository->findAllFor($this->getUser()) as $sport) {
            $sportList[(string)$sport->getId()] = $sport->getName();
        }

        if (null === $param || !isset($sportList[$param])) {
            $param = (string)$this->ConfigurationManager->getList()->getGeneral()->getRunningSport();
        }

        return new Selection($sportList, $param);
    }
}
