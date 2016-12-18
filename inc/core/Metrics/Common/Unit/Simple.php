<?php

namespace Runalyze\Metrics\Common\Unit;

use Runalyze\Metrics\Common\BaseUnitTrait;
use Runalyze\Metrics\Common\UnitInterface;

class Simple implements UnitInterface
{
    use BaseUnitTrait;

    /** @var string */
    protected $Appendix;

    /**
     * @param string $appendix
     */
    public function __construct($appendix)
    {
        $this->Appendix = $appendix;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getAppendix()
    {
        return $this->Appendix;
    }
}
