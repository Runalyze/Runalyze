<?php

namespace Runalyze\Bundle\CoreBundle\Entity\Common;

interface AccountRelatedEntityInterface
{
    /**
     * @return \Runalyze\Bundle\CoreBundle\Entity\Account
     */
    public function getAccount();
}
