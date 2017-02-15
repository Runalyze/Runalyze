<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ConfRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Conf[]
     */
    public function findByAccount(Account $account)
    {
        return $this->findBy([
            'account' => $account->getId()
        ]);
    }

    /**
     * @param Account $account
     * @param string $key
     * @return Conf|null
     */
    public function findByAccountAndKey(Account $account, $key)
    {
        $results = $this->findBy([
            'account' => $account->getId(),
            'key' => $key
        ]);

        return empty($results) ? null : $results[0];
    }

    /**
     * @param Account $account
     * @param string $category
     * @param string $key
     * @param string $value
     *
     * @return Conf
     */
    public function updateOrInsert(Account $account, $category, $key, $value)
    {
        if (null === $conf = $this->findByAccountAndKey($account, $key)) {
            $conf = new Conf();
            $conf->setCategory($category);
            $conf->setKey($key);
            $conf->setAccount($account);
        }

        $conf->setValue($value);
        $this->save($conf);

        return $conf;
    }

    public function save(Conf $conf)
    {
        $this->_em->persist($conf);
        $this->_em->flush($conf);
    }
}
