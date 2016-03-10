# [Runalyze v2.5.0-dev](http://blog.runalyze.com)

[![Build Status](https://travis-ci.org/Runalyze/Runalyze.svg?branch=master)](https://travis-ci.org/Runalyze/Runalyze)
[![Code Coverage](https://scrutinizer-ci.com/g/Runalyze/Runalyze/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Runalyze/Runalyze/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Runalyze/Runalyze/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Runalyze/Runalyze/?branch=master)
[![Translation status](http://translate.runalyze.de/widgets/runalyze/-/svg-badge.svg)](http://translate.runalyze.de/engage/runalyze/?utm_source=widget)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/Runalyze/Runalyze?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Runalyze is a web application for analyzing your training - more detailed than any other sports diary.  
Runalyze is mainly developed by [laufhannes](https://github.com/laufhannes) and [mipapo](https://github.com/mipapo).

## Documentation
We provide two different documentations: 
* [help.runalyze.com](http://help.runalyze.com) - faq for users
* [docs.runalyze.com](http://docs.runalyze.com) - docs for admins/developers
  * [Installation](http://docs.runalyze.com/en/latest/install.html) - tutorial in german [here](http://blog.runalyze.com/installation/)
  * [Update](http://docs.runalyze.com/en/latest/update.html)
  * [Checkout](http://docs.runalyze.com/en/latest/checkout.html)
  * [Contributing](http://docs.runalyze.com/en/latest/contribute.html)

Both documentations have their own repos: [docs](https://github.com/Runalyze/docs) and [admin-docs](https://github.com/Runalyze/admin-docs). In addition, there's our [runalyze-playground](https://github.com/Runalyze/runalyze-playground) to play around with some new ideas. Feel free to contribute there.

## Install / Development
Runalyze v2.1+ requires [composer](https://getcomposer.org/doc/00-intro.md#system-requirements) and
some future version will require [npm](https://nodejs.org/download/),
[bower](http://bower.io/) (`sudo npm install -g bower`) and
[grunt](http://gruntjs.com/) (`sudo npm install -g grunt-cli`). Some branches already do so.

To install dependencies and build for v2.1+:
```
composer install --prefer-dist
php build/build.php translations
```

To install dependencies and build for a version with grunt:
```
composer install --prefer-dist
bower install
npm install
grunt
```

Still, we don't have any automated migration script for the database so far.
You have to apply recent changes from the respective update files in `inc/install/` by hand.

## License
* see [#952](https://github.com/Runalyze/Runalyze/issues/952)

## Changelog
* [v2.4](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-4-en/), 25.01.2016: minor release (more weather data, recognition of duplicate activities, ...)
 * [Have a look at the update instructions](http://docs.runalyze.com/en/latest/update.html#upgrade-from-2-3-to-2-4)  
* [v2.3](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-en/), 11.12.2015: minor release (dataset refactoring, new running dynamics, ...)
 * warning: migration from v2.2 to v2.3 requires `refactor-geohash.php`
 * [v2.3.1](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-1-en/), 16.12.2015: minor bugfixes
 * [v2.3.2](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-2-en/), 25.12.2015: minor bugfixes
 * [v2.3.3](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-3-3-en/), 09.01.2016: minor bugfixes
* [v2.2](http://blog.runalyze.com/en/allgemein-en/runalyze-v2-2-en/), 28.10.2015: minor release (equipment for all sports, imperial units, ...)
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
