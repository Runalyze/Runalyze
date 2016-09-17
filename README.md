# [Runalyze v3.1.0-dev](http://blog.runalyze.com)

[![Build Status](https://travis-ci.org/Runalyze/Runalyze.svg?branch=master)](https://travis-ci.org/Runalyze/Runalyze)
[![Code Coverage](https://scrutinizer-ci.com/g/Runalyze/Runalyze/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Runalyze/Runalyze/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Runalyze/Runalyze/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Runalyze/Runalyze/?branch=master)
[![Translation status](http://translate.runalyze.de/widgets/runalyze/-/svg-badge.svg)](http://translate.runalyze.de/engage/runalyze/?utm_source=widget)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/Runalyze/Runalyze?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Runalyze is a web application for analyzing your training - more detailed than any other sports diary.
Runalyze is mainly developed by [laufhannes](https://github.com/laufhannes) and [mipapo](https://github.com/mipapo).

## Documentation
We provide two different documentations:
* [help.runalyze.com](https://help.runalyze.com) - faq for users
* [docs.runalyze.com](https://docs.runalyze.com) - docs for admins/developers
  * [Installation](https://docs.runalyze.com/en/latest/install.html) - tutorial in german [here](https://blog.runalyze.com/installation/)
  * [Update](https://docs.runalyze.com/en/latest/update.html)
  * [Checkout](https://docs.runalyze.com/en/latest/checkout.html)
  * [Contributing](https://docs.runalyze.com/en/latest/contribute.html)

Both documentations have their own repos: [docs](https://github.com/Runalyze/docs) and [admin-docs](https://github.com/Runalyze/admin-docs). In addition, there's our [runalyze-playground](https://github.com/Runalyze/runalyze-playground) to play around with some new ideas. Feel free to contribute there.

## Install / Development
Runalyze requires [composer](https://getcomposer.org/doc/00-intro.md#system-requirements) and
[npm](https://nodejs.org/download/)
(plus [bower](http://bower.io/) and
[gulp](https://github.com/gulpjs/gulp/blob/master/docs/getting-started.md), will be installed via npm).

To install dependencies and build:
```
composer install
npm install
gulp
```


## License
* see [#952](https://github.com/Runalyze/Runalyze/issues/952)

## Changelog
* [v3.0](https://blog.runalyze.com/en/allgemein-en/runalyze-v3-0-en/), [[update instructions]](https://docs.runalyze.com/en/latest/upgrade/3.x.html#upgrade-from-2-6-to-3-0), 28.08.2016: major release (improved core, no amazing new features)
* [v2.6](https://blog.runalyze.com/en/allgemein-en/runalyze-v2-6-en/), [[update instructions]](http://docs.runalyze.com/en/latest/upgrade/2.x.html#upgrade-from-2-5-to-2-6), 22.05.2016: minor release (race results, improved weather cache ...)
 * [v2.6.1](https://blog.runalyze.com/en/allgemein-en/runalyze-v2-6-1-en/), 31.05.2016: minor bugfixes
 * [v2.6.2](https://blog.runalyze.com/en/allgemein-en/runalyze-v2-6-2-en/), 18.07.2016: minor bugfixes
 * [v2.6.3](https://blog.runalyze.com/en/allgemein-en/runalyze-v2-6-3-en/), 03.08.2016: minor bugfixes
 * [v2.6.4](https://blog.runalyze.com/en/allgemein-en/runalyze-v2-6-4-en/), 17.08.2016: minor bugfixes
 * [v2.6.5](https://blog.runalyze.com/en/allgemein-en/runalyze-v2-6-5-en/), 22.08.2016: minor bugfixes
 * v2.6.6, 28.08.2016: fix fit file import with developer fields
* [v2.5](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-5-en/), [[update instructions]](http://docs.runalyze.com/en/latest/upgrade/2.x.html#upgrade-from-2-4-to-2-5), 12.04.2016: minor release (timezone support, Moving average, ...)
 * [v2.5.1](https://blog.runalyze.com/en/allgemein-en/runalyze-v2-5-1-en/), 24.04.2016: minor bugfixes
 * [v2.5.2](https://blog.runalyze.com/en/allgemein-en/runalyze-v2-5-2-en/), 07.05.2016: minor bugfixes
 * [v2.5.3](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-5-3-en/), 31.05.2016: minor bugfixes
* [v2.4](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-4-en/), [[update instructions]](http://docs.runalyze.com/en/latest/upgrade/2.x.html#upgrade-from-2-3-to-2-4), 25.01.2016: minor release (more weather data, recognition of duplicate activities, ...)
 * [v2.4.1](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-4-1-en/), 09.02.2016: minor bugfixes
 * [v2.4.2](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-4-2-en/), 29.03.2016: minor bugfixes
* [v2.3](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-en/), [[update instructions]](http://docs.runalyze.com/en/latest/upgrade/2.x.html#upgrade-from-2-2-to-2-3), 11.12.2015: minor release (dataset refactoring, new running dynamics, ...)
 * warning: migration from v2.2 to v2.3 requires `refactor-geohash.php`
 * [v2.3.1](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-1-en/), 16.12.2015: minor bugfixes
 * [v2.3.2](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-2-en/), 25.12.2015: minor bugfixes
 * [v2.3.3](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-3-en/), 09.01.2016: minor bugfixes
 * [v2.3.4](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-4-en/), 29.03.2016: minor bugfixes
* [v2.2](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-2-en/), [[update instructions]](http://docs.runalyze.com/en/latest/upgrade/2.x.html#upgrade-from-2-1-to-2-2), 28.10.2015: minor release (equipment for all sports, imperial units, ...)
 * If you're updating from 2.1.* directly use v2.2.1 for updating
 * warning: migration from v2.1 to v2.2 requires `refactor-equipment.php`
 * [v2.2.1] (http://blog.runalyze.com/en/allgemein-en/runalyze-v2-1-1-en/), 18.11.2015: minor bugfixes
 * [v2.2.2] (http://blog.runalyze.com/en/allgemein-en/runalyze-v2-2-2-en/), 10.12.2015: minor bugfixes
* [v2.1](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-1-en/), 19.07.2015: minor release (running dynamics, new importers, recovery time ...)
 * [v2.1.1](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-1-1-en/), 22.10.2015: minor bugfixes
* [v2.0](http://blog.runalyze.com/allgemein/runalyze-v2-0/), 28.02.2015: first mutlilingual major release
 * warning: migration from v1.5 to v2.0 requires `refactor-db.php` (see [v2.0alpha](http://blog.runalyze.com/allgemein/runalyze-v2-0alpha/), [v2.0beta](http://blog.runalyze.com/allgemein/runalyze-v2-0beta/))
 * [v2.0.1](http://blog.runalyze.com/allgemein/runalyze-v2-0-1/), 13.03.2015: minor bugfixes
 * [v2.0.2](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-0-2-2/), 24.03.2015: minor bugfixes
 * [v2.0.3](http://blog.runalyze.com/allgemein/runalyze-v2-0-3/), 05.06.2015: minor bugfixes
 * [v2.0.4](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-0-4-2/), 17.07.2015: minor bugfixes
* old versions (german only)
 * [v1.5](http://blog.runalyze.com/allgemein/runalyze-v1-5/), 01.01.2014: bugfixes, improved vdot formula
 * [v1.4](http://blog.runalyze.com/allgemein/runalyze-v1-4-fix-fuer-sicherheitsproblem/), 23.08.2013: bugfix for security issue
 * [v1.3](http://blog.runalyze.com/allgemein/runalyze-v1-3/), 29.07.2013: new importer, more data, vdot correction by elevation, ...
 * [v1.2](http://blog.runalyze.com/allgemein/runalyze-v1-2/), 13.11.2012: save plots, share activity list
 * [v1.1](http://blog.runalyze.com/allgemein/runalyze-v1-1/), 19.07.2012: first online version
 * [v1.0](http://blog.runalyze.com/allgemein/runalyze-v1-0/), 20.01.2012: first public version

## Credits
* Icons
	* [Font Awesome](http://fontawesome.io/) by Dave Gandy
	* [Forecast Font](http://forecastfont.iconvau.lt/) by Ali Sisk
	* [Icons8 Font](http://icons8.com/) by VisualPharm
* Elevation data from Shuttle Radar Topography Mission
	* [SRTM tiles](http://dwtkns.com/srtm/) grabbed via Derek Watkins
	* [SRTM files](http://srtm.csi.cgiar.org/) by International  Centre for Tropical  Agriculture (CIAT)
	* [SRTMGeoTIFFReader](http://www.osola.org.uk/elevations/index.htm) by Bob Osola
* [jQuery](http://jquery.org/) by jQuery Foundation, Inc.
    * [Bootstrap Tooltip](http://twitter.github.com/bootstrap/javascript.html#tooltips) by Twitter, Inc.
    * [Flot](http://www.flotcharts.org/) by IOLA and Ole Laursen
    * [Leaflet](http://leafletjs.com/) by Vladimir Agafonkin
    * [FineUploader](https://github.com/Widen/fine-uploader) by Widen Enterprises, Inc.
    * [Tablesorter](http://tablesorter.com/docs/) by Christian Bach
    * [Datepicker](http://www.eyecon.ro/) by Stefan Petre
    * [Chosen](http://getharvest.com/) by Patrick Filler for Harvest
    * [FontIconPicker](http://codeb.it/) by Alessandro Benoit &amp; Swashata Ghosh
* Miscellaneaous
    * [phpFastCache](https://github.com/khoaofgod/phpfastcache) by Khoa Bui
    * [Garmin Communicator](http://developer.garmin.com/web-device/garmin-communicator-plugin/) by Garmin Ltd.
    * [Weather data](http://openweathermap.org) from OpenWeatherMap Inc.
