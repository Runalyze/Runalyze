<?php

namespace Runalyze\Bundle\CoreBundle\Component\Statistics\MonthlyStats;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Selection\Selection;
use Runalyze\Bundle\CoreBundle\Services\Selection\SportSelectionFactory;
use Runalyze\Bundle\CoreBundle\Twig\DisplayableTime;
use Runalyze\Bundle\CoreBundle\Twig\DisplayableValue;
use Runalyze\Bundle\CoreBundle\Twig\ValueExtension;

class AnalysisData
{
    /** @var int|float */
    protected $Maximum = 1;

    /** @var array */
    protected $Data = [];

    /** @var Selection  */
    protected $SportSelection;

    /** @var AnalysisSelection */
    protected $AnalysisSelection;

    /** @var DisplayableValue */
    protected $DefaultValue;

    public function __construct(
        Selection $sportSelection,
        AnalysisSelection $analysisSelection,
        TrainingRepository $trainingRepository,
        Account $account
    )
    {
        $this->SportSelection = $sportSelection;
        $this->AnalysisSelection = $analysisSelection;

        $this->fetchDataFrom($trainingRepository, $account);
    }

    protected function fetchDataFrom(TrainingRepository $trainingRepository, Account $account)
    {
        $stats = $trainingRepository->getMonthlyStatsFor($account, $this->getColumn(), $this->getSportId());

        foreach ($stats as $result) {
            $year = (int)$result['year'];
            $month = (int)$result['month'];
            $value = (float)$result['value'];

            if (!isset($this->Data[$year])) {
                $this->Data[$year] = array_fill(1, 12, 0.0);
            }

            $this->Data[$year][$month] = $value;

            if ($value > $this->Maximum) {
                $this->Maximum = $value;
            }
        }
    }

    /**
     * @return string|null
     */
    protected function getColumn()
    {
        switch ($this->AnalysisSelection->getCurrentKey()) {
            case AnalysisSelection::DISTANCE:
                return 'distance';
            case AnalysisSelection::TIME:
                return 's';
            case AnalysisSelection::ENERGY:
                return 'kcal';
            case AnalysisSelection::ELEVATION:
                return 'elevation';
            case AnalysisSelection::TRIMP:
                return 'trimp';
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    protected function getSportId()
    {
        if (!$this->SportSelection->hasCurrentKey() || SportSelectionFactory::ALL == $this->SportSelection->getCurrentKey()) {
            return null;
        }

        return $this->SportSelection->getCurrentKey();
    }

    /**
     * @param ValueExtension $valueExtension
     */
    public function setDefaultValue(ValueExtension $valueExtension)
    {
        $this->DefaultValue = $this->getDefaultValue($valueExtension);
    }

    /**
     * @param ValueExtension $valueExtension
     * @return DisplayableValue
     */
    protected function getDefaultValue(ValueExtension $valueExtension)
    {
        switch ($this->AnalysisSelection->getCurrentKey()) {
            case AnalysisSelection::DISTANCE:
                return $valueExtension->distance(0.0);
            case AnalysisSelection::TIME:
                return new DisplayableTime(0);
            case AnalysisSelection::ENERGY:
                return $valueExtension->energy(0);
            case AnalysisSelection::ELEVATION:
                return $valueExtension->elevation(0);
        }

        return new DisplayableValue(0, '');
    }

    /**
     * @return Selection
     */
    public function getSportSelection()
    {
        return $this->SportSelection;
    }

    /**
     * @return AnalysisSelection
     */
    public function getAnalysisSelection()
    {
        return $this->AnalysisSelection;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->Data);
    }

    /**
     * @return array
     */
    public function getYears()
    {
        $allYears = array_keys($this->Data);

        return range(max($allYears), min($allYears));
    }

    /**
     * @param int $year
     * @param int $month
     * @return DisplayableValue
     */
    public function getValue($year, $month)
    {
        $this->DefaultValue->setRawValue($this->getRawValue($year, $month));

        return $this->DefaultValue;
    }

    /**
     * @param int $year
     * @param int $month
     * @return float
     */
    public function getRawValue($year, $month)
    {
        if (isset($this->Data[$year]) && isset($this->Data[$year][$month])) {
            return $this->Data[$year][$month];
        }

        return 0.0;
    }

    /**
     * @return float|int
     */
    public function getMaximum()
    {
        return $this->Maximum;
    }
}
