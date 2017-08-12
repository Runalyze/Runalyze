# [RUNALYZE v4.3.0-dev](https://blog.runalyze.com)

[![Build Status](https://travis-ci.org/Runalyze/Runalyze.svg?branch=master)](https://travis-ci.org/Runalyze/Runalyze)
[![Code Coverage](https://scrutinizer-ci.com/g/Runalyze/Runalyze/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Runalyze/Runalyze/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Runalyze/Runalyze/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Runalyze/Runalyze/?branch=master)
[![Translation status](https://translate.runalyze.com/widgets/runalyze/-/svg-badge.svg)](http://translate.runalyze.de/engage/runalyze/?utm_source=widget)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/Runalyze/Runalyze?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=97LV7VEAG4KK6)

[Runalyze](https://blog.runalyze.com) is a web application for analyzing your training - more detailed than any other sports diary.
We are offering a official hosted version at [runalyze.com](https://runalyze.com).
Runalyze is mainly developed by [laufhannes](https://github.com/laufhannes) and [mipapo](https://github.com/mipapo).

## Documentation
We provide two different documentations:
* [help.runalyze.com](https://help.runalyze.com) - faq for users
* [docs.runalyze.com](https://docs.runalyze.com) - docs for admins/developers
  * [Installation](https://docs.runalyze.com/en/latest/installation/3.x.html)
  * [Update](https://docs.runalyze.com/en/latest/upgrade/3.x.html)
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

## Features
 * import activity files (*.fit, *.tcx, *.gpx and many more)
 * TRIMP principle
 * long-term form analysis
 * VO2max estimation
 * race prediction based on your shape
 * statistics like *Monotony, training strain, stress balance*
 * heart rate variability (HRV) in activity view
 * elevation correction and calculation
 * ...

Look at [help.runalyze.com](https://help.runalyze.com/latest/features.html) for a feature list with screenshots.


## Support
You are welcome to ask questions (regarding update/installation/calculations and new ideas) in our [forum](https://forum.runalyze.com) in English or German. For short questions you may use [Gitter](https://gitter.im/Runalyze/Runalyze) or [Twitter](https://twitter.com/RunalyzeDE).

## Translation

Please use our official translation platform at [translate.runalyze.com](https://translate.runalyze.com). Pull requests for translations files will be ignored. Open a new issue for adding a language which is not available for translation yet.

## License
Yep, we know that we have to add a `LICENSE.md` and `CONTRIBUTING.md` to our repository. Finally we need to setup a CLA. These things take time and we are really busy developing new things for RUNALYZE.
 (see discussion at [#952](https://github.com/Runalyze/Runalyze/issues/952))

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to us at support@runalyze.com.

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
