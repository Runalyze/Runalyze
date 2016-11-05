<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class ActivityView extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'GMAP_PATH_PRECISION' => '5',
            'GMAP_PATH_BREAK' => '15',
            'TRAINING_MAP_COLOR' => '#FF5500',
            'TRAINING_LEAFLET_LAYER' => 'OpenStreetMap',
            'TRAINING_MAP_SHOW_FIRST' => 'false',
            'TRAINING_MAP_ZOOM_ON_SCROLL' => 'false',
            'TRAINING_PLOT_SMOOTH' => 'false',
            'TRAINING_PLOT_XAXIS_TIME' => 'false',
            'TRAINING_PLOT_MODE' => 'all',
            'TRAINING_PLOT_PRECISION' => '200points',
            'TRAINING_PLOT_SPLITS_ZERO' => 'true',
            'PACE_Y_LIMIT_MIN' => '0',
            'PACE_Y_LIMIT_MAX' => '0',
            'PACE_Y_AXIS_TYPE' => 'AS_SPEED',
            'PACE_HIDE_OUTLIERS' => 'false',
            'TRAINING_DECIMALS' => '1',
            'SHOW_SECTIONS_FULLHEIGHT' => 'false',
            'ELEVATION_METHOD' => 'treshold',
            'ELEVATION_TRESHOLD' => '3',
        ];
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\ActivityView::class;
    }
}
