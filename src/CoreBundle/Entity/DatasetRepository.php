<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DatasetRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @return Dataset[]
     */
    public function findAllFor(Account $account)
    {

        return $this->findBy(
            ['account' => $account->getId()],
            ['position' => 'ASC']);

    }


    public function save(Dataset $dataset)
    {
        $this->_em->persist($dataset);
        $this->_em->flush();
    }

    public function delete(Dataset $dataset)
    {
        $this->_em->remove($dataset);
        $this->_em->flush();
    }
}
