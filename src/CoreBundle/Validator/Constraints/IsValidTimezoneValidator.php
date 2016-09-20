<?php
namespace Runalyze\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Runalyze\Parameter\Application\Timezone as Timezone;

class IsValidTimezoneValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ( 0 == Timezone::isValidValue($value) ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}