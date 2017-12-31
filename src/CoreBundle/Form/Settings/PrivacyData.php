<?php

namespace Runalyze\Bundle\CoreBundle\Form\Settings;

use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\Privacy;

class PrivacyData
{
    /** @var bool */
    public $AthletePageActive = false;

    /** @var bool */
    public $ShowPrivateInList = false;

    /** @var bool */
    public $ShowStatisticsInList = false;

    /** @var string never|race|always */
    public $MapPrivacy = 'always';

    public function setDataFrom(Privacy $privacy)
    {
        $this->AthletePageActive = $privacy->isListPublic();
        $this->ShowPrivateInList = $privacy->isListShowingAllActivities();
        $this->ShowStatisticsInList = $privacy->isListWithStatistics();
        $this->MapPrivacy = $privacy->get('TRAINING_MAP_PUBLIC_MODE');
    }

    /**
     * @return array
     */
    public function getDataForConfiguration()
    {
        return [
            'TRAINING_LIST_PUBLIC' => $this->AthletePageActive ? 'true' : 'false',
            'TRAINING_LIST_ALL' => $this->ShowPrivateInList ? 'true' : 'false',
            'TRAINING_LIST_STATISTICS' => $this->ShowStatisticsInList ? 'true' : 'false',
            'TRAINING_MAP_PUBLIC_MODE' => $this->MapPrivacy
        ];
    }
}
