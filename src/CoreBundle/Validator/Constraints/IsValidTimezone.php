<?php
namespace Runalyze\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsValidTimezone extends Constraint
{
    public $message = 'Invalid timezone';
}
