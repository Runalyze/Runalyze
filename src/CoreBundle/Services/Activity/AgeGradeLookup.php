<?php

namespace Runalyze\Bundle\CoreBundle\Services\Activity;

use Runalyze\AgeGrade\Lookup;
use Runalyze\AgeGrade\Table\FemaleTable;
use Runalyze\AgeGrade\Table\MaleTable;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Services\TokenStorageAwareServiceTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AgeGradeLookup
{
    use TokenStorageAwareServiceTrait;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @param Account|null $account
     * @return bool
     */
    public function isLookupPossible(Account $account = null)
    {
        $account = $account ?: $this->getUser();

        return null !== $account && $account->knowsGender() && $account->knowsBirthYear();
    }

    /**
     * @param Account|null $account
     * @return null|Lookup
     */
    public function getLookup(Account $account = null)
    {
        if (!$this->isLookupPossible($account)) {
            return null;
        }

        $account = $account ?: $this->getUser();

        return new Lookup(
            $account->isFemale() ? new FemaleTable() : new MaleTable(),
            $account->getAge()
        );
    }

    /**
     * @return Lookup
     */
    public function getDefaultLookup()
    {
        return new Lookup(new MaleTable(), 25);
    }
}
