<?php

namespace Runalyze\Parser\Activity;

use Runalyze\Parser\Activity\FileType;

class FileExtensionToParserMapping
{
    /** @var string[] */
    const MAPPING = [
        'csv' => FileType\Csv::class,
        'fitlog' => FileType\Fitlog::class,
        'fittemp' => FileType\Fit::class,
        'gpx' => FileType\Gpx::class,
        'hrm' => FileType\Hrm::class,
        'kml' => FileType\Kml::class,
        'logbook' => FileType\Logbook::class,
        'pwx' => FileType\Pwx::class,
        'slf' => FileType\Slf::class,
        'slf3' => FileType\Slf3::class,
        'slf4' => FileType\Slf4::class,
        'sml' => FileType\Sml::class,
        'tcx' => FileType\Tcx::class,
        'trk' => FileType\Trk::class,
        'xml' => FileType\Xml::class
    ];

    /**
     * @param string $extension
     *
     * @return string|null
     */
    public function getParserClassFor($extension)
    {
        $extension = mb_strtolower($extension);

        if (array_key_exists($extension, self::MAPPING)) {
            return self::MAPPING[$extension];
        }

        return null;
    }
}
