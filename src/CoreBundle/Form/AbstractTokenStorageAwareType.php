<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;

abstract class AbstractTokenStorageAwareType extends AbstractType
{
    use TokenStorageAwareTypeTrait;
}
