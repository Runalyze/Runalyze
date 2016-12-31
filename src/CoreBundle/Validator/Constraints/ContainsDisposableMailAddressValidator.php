<?php
namespace Runalyze\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContainsDisposableMailAddressValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ((new \EmailValidator\Validator())->isDisposable($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
