<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup;

use Runalyze\Calculation\BasicEndurance;
use Runalyze\Configuration;
use Runalyze\Model\Equipment;

class JobGeneral extends Job
{
    /** @var string */
    const INTERNALS = 'internals';

    /** @var string */
    const EQUIPMENT = 'equipment';

    /** @var string */
    const VO2MAX = 'vo2max';

    /** @var string */
    const VO2MAX_CORRECTOR = 'vo2maxCorrector';

    /** @var string */
    const ENDURANCE = 'endurance';

    /** @var string */
    const MAX_TRIMP = 'trimp';

    /** @var string */
    const CACHECLEAN = 'cacheclean';

    public function run()
    {
        if ($this->isRequested(self::INTERNALS)) {
            $this->recalculateInternalConstants();
        }

        if ($this->isRequested(self::EQUIPMENT)) {
            $this->recalculateEquipmentStatistics();
        }

        if ($this->isRequested(self::VO2MAX_CORRECTOR)) {
            $this->recalculateVO2maxcorrector();
        }

        if ($this->isRequested(self::VO2MAX)) {
            $this->recalculateVO2maxshape();
        }

        if ($this->isRequested(self::ENDURANCE)) {
            $this->recalculateBasicEndurance();
        }

        if ($this->isRequested(self::MAX_TRIMP)) {
            $this->recalculateMaximalPerformanceValues();
        }

        if ($this->isRequested(self::CACHECLEAN)) {
            $this->clearCache();
        }
    }

    protected function recalculateInternalConstants()
    {
        \Helper::recalculateStartTime();
        \Helper::recalculateHFmaxAndHFrest();

        $this->addMessage(__('Internal constants have been refreshed.'));
    }

    protected function recalculateEquipmentStatistics()
    {
        $Updater = new Equipment\StatisticsUpdater($this->PDO, $this->AccountId, $this->DatabasePrefix);
        $num = $Updater->run();

        if ($num === false) {
            $this->addMessage(__('There was a problem while recalculating your equipment statistics'));
        } else {
            $this->addMessage(sprintf(__('Statistics have been recalculated for all <strong>%s</strong> pieces of equipment.'), $num));
        }
    }

    protected function recalculateVO2maxshape()
    {
        $oldValue = Configuration::Data()->vo2maxShape();
        $newValue = Configuration::Data()->recalculateVO2maxShape();

        $this->addSuccessMessage(__('VO<sub>2</sub>max shape'), number_format($oldValue, 1), number_format($newValue, 1));
    }

    protected function recalculateVO2maxcorrector()
    {
        $oldValue = Configuration::Data()->vo2maxCorrector();
        $newValue = Configuration::Data()->recalculateVO2maxCorrector();

        $this->addSuccessMessage(__('VO<sub>2</sub>max corrector'), number_format($oldValue, 4), number_format($newValue, 4));
    }

    protected function recalculateBasicEndurance()
    {
        $oldValue = Configuration::Data()->basicEndurance();
        BasicEndurance::recalculateValue();
        $newValue = Configuration::Data()->basicEndurance();

        $this->addSuccessMessage(__('Marathon shape'), $oldValue, $newValue);
    }

    protected function recalculateMaximalPerformanceValues()
    {
        $Data = Configuration::Data();

        $oldCTL = $Data->maxCTL();
        $oldATL = $Data->maxATL();
        $oldTRIMP = $Data->maxTrimp();

        $Data->recalculateMaxValues();

        $newCTL = $Data->maxCTL();
        $newATL = $Data->maxATL();
        $newTRIMP = $Data->maxTrimp();

        $this->addSuccessMessage(__('Maximal CTL'), $oldCTL, $newCTL);
        $this->addSuccessMessage(__('Maximal ATL'), $oldATL, $newATL);
        $this->addSuccessMessage(__('Maximal TRIMP'), $oldTRIMP, $newTRIMP);
    }

    protected function clearCache()
    {
        \Cache::clean();

        $this->addMessage(__('Your personal cache has been cleared.'));
    }
}
