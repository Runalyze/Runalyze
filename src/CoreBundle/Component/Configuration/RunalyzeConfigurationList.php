<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration;

use Runalyze\Bundle\CoreBundle\Component\Configuration\Category\AbstractCategory;

class RunalyzeConfigurationList extends ConfigurationList
{
    /** @var array ['key' => 'className'] */
    protected $CategoryMap = [];

    /** @var AbstractCategory[] */
    protected $CategoryPool = [];

    /** @var UnitSystem|null */
    protected $UnitSystem = null;

    public function __construct(array $variables = [])
    {
        parent::__construct([]);

        $this->setCategoryMap();
        $this->loadDefaultVariablesFromCategories();

        if (!empty($variables)) {
            $this->mergeWith($variables);
        }
    }

    public function mergeWith(array $variables)
    {
        parent::mergeWith($variables);

        $this->CategoryPool = [];
    }

    public function set($key, $value)
    {
        parent::set($key, $value);

        $categoryName = substr($key, 0, strpos('.', $key));

        if (isset($this->CategoryPool[$categoryName])) {
            $this->CategoryPool[$categoryName]->set(substr($key, strlen($categoryName) + 1), $value);
        }
    }

    protected function setCategoryMap()
    {
        $this->CategoryMap = [
            'activity-form' => Category\ActivityForm::class,
            'activity-view' => Category\ActivityView::class,
            'basic-endurance' => Category\BasicEndurance::class,
            'data' => Category\Data::class,
            'data-browser' => Category\DataBrowser::class,
            'design' => Category\Design::class,
            'general' => Category\General::class,
            'misc' => Category\Miscellaneous::class,
            'privacy' => Category\Privacy::class,
            'trimp' => Category\Trimp::class,
            'vdot' => Category\Vdot::class,
        ];
    }

    protected function loadDefaultVariablesFromCategories()
    {
        foreach ($this->CategoryMap as $categoryKey => $className) {
            /** @var AbstractCategory $category */
            $category = new $className;

            foreach ($category->getDefaultVariables() as $key => $defaultValue) {
                $this->Variables[$categoryKey.'.'.$key] = $defaultValue;
            }
        }
    }

    /**
     * @param string $categoryName
     * @return Category\AbstractCategory This category is not persistent, it's created on each call.
     */
    protected function getCategory($categoryName)
    {
        if (!isset($this->CategoryPool[$categoryName])) {
            if (!isset($this->CategoryMap[$categoryName])) {
                throw new \InvalidArgumentException('Unknown category key "'.$categoryName.'".');
            }

            if (!class_exists($this->CategoryMap[$categoryName]) || !is_subclass_of($this->CategoryMap[$categoryName], AbstractCategory::class)) {
                throw new \LogicException('Category must be mapped to an class extending AbstractCategory.');
            }

            $this->CategoryPool[$categoryName] = new $this->CategoryMap[$categoryName]($this->getDataForPrefix($categoryName));
        }

        return $this->CategoryPool[$categoryName];
    }

    /**
     * @param string $prefix
     * @return array
     */
    protected function getDataForPrefix($prefix)
    {
        $prefix .= '.';
        $prefixLength = strlen($prefix);
        $data = [];

        foreach ($this->Variables as $key => $value) {
            if (substr($key, 0, $prefixLength) === $prefix) {
                $data[substr($key, $prefixLength)] = $value;
            }
        }

        return $data;
    }

    /**
     * @return Category\ActivityForm This category is not persistent, it's created on each call.
     */
    public function getActivityForm()
    {
        return $this->getCategory('activity-form');
    }

    /**
     * @return Category\ActivityView This category is not persistent, it's created on each call.
     */
    public function getActivityView()
    {
        return $this->getCategory('activity-view');
    }

    /**
     * @return Category\BasicEndurance This category is not persistent, it's created on each call.
     */
    public function getBasicEndurance()
    {
        return $this->getCategory('basic-endurance');
    }

    /**
     * @return Category\Data This category is not persistent, it's created on each call.
     */
    public function getData()
    {
        return $this->getCategory('data');
    }

    /**
     * @return Category\DataBrowser This category is not persistent, it's created on each call.
     */
    public function getDataBrowser()
    {
        return $this->getCategory('data-browser');
    }

    /**
     * @return Category\Design This category is not persistent, it's created on each call.
     */
    public function getDesign()
    {
        return $this->getCategory('design');
    }

    /**
     * @return Category\General This category is not persistent, it's created on each call.
     */
    public function getGeneral()
    {
        return $this->getCategory('general');
    }

    /**
     * @return Category\Miscellaneous This category is not persistent, it's created on each call.
     */
    public function getMiscellaneous()
    {
        return $this->getCategory('misc');
    }

    /**
     * @return Category\Privacy This category is not persistent, it's created on each call.
     */
    public function getPrivacy()
    {
        return $this->getCategory('privacy');
    }

    /**
     * @return Category\Trimp This category is not persistent, it's created on each call.
     */
    public function getTrimp()
    {
        return $this->getCategory('trimp');
    }

    /**
     * @return Category\Vdot This category is not persistent, it's created on each call.
     */
    public function getVdot()
    {
        return $this->getCategory('vdot');
    }

    /**
     * @return UnitSystem
     */
    public function getUnitSystem()
    {
        if (null === $this->UnitSystem) {
            $this->UnitSystem = new UnitSystem($this);
        }

        return $this->UnitSystem;
    }

    /**
     * @return float
     */
    public function getVdotFactor()
    {
        if (is_numeric($this->Variables['vdot.VDOT_MANUAL_CORRECTOR'])) {
            return (float)$this->Variables['vdot.VDOT_MANUAL_CORRECTOR'];
        }

        return (float)$this->Variables['data.VDOT_CORRECTOR'];
    }

    /**
     * @return bool
     */
    public function useVdotCorrectionForElevation()
    {
        return ('true' == $this->Variables['vdot.VDOT_USE_CORRECTION_FOR_ELEVATION']);
    }

    /**
     * @return float
     */
    public function getCurrentVdot()
    {
        if (is_numeric($this->Variables['vdot.VDOT_MANUAL_VALUE'])) {
            return (float)$this->Variables['vdot.VDOT_MANUAL_VALUE'];
        }

        return (float)$this->Variables['data.VDOT_FORM'];
    }
}
