<?php
require_once '../class.Frontend.php';

$Frontend = new Frontend();
?>
<div class="panel-heading">
	<h1>Runalyze</h1>
</div>
<div class="panel-content">
	<p class="text">
		<?php _e('Runalyze is completly configurable and as detailed as no other tool for analyzing your activities.'); ?>
	</p>

	<p class="text">
		<?php printf( __('Please look at our official website %s to get an overview of ower features.'), '<a href="http://runalyze.de/">runalyze.de</a>'); ?><br>
		<?php printf( __('If you want to be informed about all changes and some hints, visit our blog at %s.'), '<a href="http://runalyze.de/blog/">runalyze.de/blog/</a>'); ?>
	</p>

	<p class="text">
		<?php _e('Runalyze is an open-source project. We are working on it as much as we can in our free time.'); ?><br>
		<?php _e('Official developers:'); ?>
		<a href="http://www.laufhannes.de/" title="Laufblog: Laufhannes">Hannes Christiansen</a>,
		<a href="http://mipapo.de/" title="Mipapo: Michael Pohl">Michael Pohl</a>
	</p>
</div>


<div class="panel-heading panel-sub-heading">
	<h1><?php _e('Configuration'); ?></h1>
</div>
<div class="panel-content">
	<p class="text">
		<?php _e('The main advantage of Runalyze is the ability to adapt everything to our personal needs and wishes.'); ?>
		<?php _e('Have a look at the configuration window to see what we\'re talking about.'); ?>
		<?php _e('You can define your own sports or activity types and choose your own way of presentation and configure some parameters of our experimental calculations.'); ?>
	</p>
</div>


<div class="panel-heading panel-sub-heading">
	<h1><?php _e('Support'); ?></h1>
</div>
<div class="panel-content">
	<p class="text">
		<?php _e('Please let us know if you have wishes or observe some bugs.'); ?>
		<?php _e('We give our best to make Runalyze as pleasant for you as possible. - Therefore we have to know what you want.'); ?>
	</p>

	<p class="text">
		<?php _e('You can contact us via mail:'); ?> <a href="mailto:support@runalyze.de" title="Support">support@runalyze.de</a>.
	</p>

	<ul class="blocklist blocklist-inline clearfix">
		<li><a href="https://github.com/Runalyze/Runalyze" title="GitHub: Runalyze"><i class="fa fa-github-alt"></i> <strong>GitHub</strong></a></li>
		<li><a href="http://twitter.com/RunalyzeDE" title="Runalyze"><i class="fa fa-twitter color-twitter"></i> <strong>Twitter</strong></a></li>
		<li><a href="http://facebook.com/Runalyze" title="Runalyze"><i class="fa fa-facebook color-facebook"></i> <strong>Facebook</strong></a></li>
		<li><a href="https://plus.google.com/communities/116260192529858591171" title="Runalyze"><i class="fa fa-google-plus color-google-plus"></i> <strong>Google+</strong></a></li>
		<li><a href="http://runalyze.de/faq/" title="Runalyze"><i class="fa fa-question"></i> <strong>FAQ</strong></a></li>
	</ul>
</div>


<div class="panel-heading panel-sub-heading">
	<h1>Changelog</h1>
</div>
<div class="panel-content">
	<ul>
		<li>
			<strong>Version 2.x</strong>, 2014
			<ul>
				<li><strong><a href="http://runalyze.de/allgemein/runalyze-v2-0/" title="Runalyze v2.0">v2.0</a></strong>, XX.XX.2014: ...</li>
			</ul>
		</li>
		<li>
			<strong>Version 1.x</strong>, 2012 - 2013, <em>(only in german)</em>
			<ul>
				<li><strong><a href="http://runalyze.de/allgemein/runalyze-v1-5/" title="Runalyze v1.5">v1.5</a></strong>, 01.01.2014: Bugfixes, genauere VDOT-Formel</li>
				<li><strong><a href="http://runalyze.de/allgemein/runalyze-v1-4-fix-fuer-sicherheitsproblem/" title="Runalyze v1.4">v1.4</a></strong>, 23.08.2013: Bugfix für Sicherheitsrisiko</li>
				<li><strong><a href="http://runalyze.de/allgemein/runalyze-v1-3/" title="Runalyze v1.3">v1.3</a></strong>, 29.07.2013: Neue Importer, mehr Trainingsdaten, VDOT-Korrektur für H&ouml;henmeter, ...</li>
				<li><strong><a href="http://runalyze.de/allgemein/runalyze-v1-2/" title="Runalyze v1.2">v1.2</a></strong>, 13.11.2012: Diagramme speichern, &Ouml;ffentliche Trainingsliste</li>
				<li><strong><a href="http://runalyze.de/allgemein/runalyze-v1-1/" title="Runalyze v1.1">v1.1</a></strong>, 19.07.2012: Erste Online-Version</li>
				<li><strong><a href="http://runalyze.de/allgemein/runalyze-v1-0/" title="Runalyze v1.0">v1.0</a></strong>, 20.01.2012: Erste &ouml;ffentliche Version</li>
			</ul>
		</li>
	</ul>
</div>


<div class="panel-heading panel-sub-heading">
	<h1>Credits</h1>
</div>
<div class="panel-content">
	<ul>
		<li>
			<strong>Icons</strong>
			<ul>
				<li>Font Awesome by Dave Gandy - <a class="external" href="http://fontawesome.io">http://fontawesome.io</a></li>
			</ul>
		</li>
		<li>
			<strong>Elevation data from Shuttle Radar Topography Mission</strong>
			<ul>
				<li>SRTM tiles grabbed via Derek Watkins - <a class="external" href="http://dwtkns.com/srtm/">http://dwtkns.com/srtm/</a></li>
				<li>SRTM files by International Centre for Tropical  Agriculture (CIAT) - <a class="external" href="http://srtm.csi.cgiar.org">http://srtm.csi.cgiar.org</a></li>
				<li>SRTMGeoTIFFReader by Bob Osola - <a class="external" href="http://www.osola.org.uk/elevations/index.htm">http://www.osola.org.uk/elevations/index.htm</a></li>
			</ul>
		</li>
		<li>
			<strong>jQuery</strong> by jQuery Foundation, Inc. - <a class="external" href="http://jquery.org/">http://jquery.org/</a>
			<ul>
				<li>Bootstrap Tooltip by Twitter, Inc. - <a class="external" href="http://twitter.github.com/bootstrap/javascript.html#tooltips">http://twitter.github.com/bootstrap/javascript.html#tooltips</a></li>
				<li>Flot by IOLA and Ole Laursen - <a class="external" href="http://www.flotcharts.org/">http://www.flotcharts.org/</a></li>
				<li>Leaflet by Vladimir Agafonkin - <a class="external" href="http://leafletjs.com/">http://leafletjs.com/</a></li>
				<li>FineUploader by Widen Enterprises, Inc. <a class="external" href="https://github.com/Widen/fine-uploader">https://github.com/Widen/fine-uploader</a>
				<li>Tablesorter by Christian Bach - <a class="external" href="http://tablesorter.com/docs/">http://tablesorter.com/docs/</a></li>
				<li>Datepicker by Stefan Petre - <a class="external" href="http://www.eyecon.ro/">http://www.eyecon.ro/</a></li>
				<li>Chosen by Patrick Filler for Harvest - <a class="external" href="http://getharvest.com">http://getharvest.com</a></li>
			</ul>
		</li>
		<li>
			<strong>Miscellaneaous</strong>
			<ul>
				<li>Garmin Communicator by Garmin Ltd. - <a class="external" href="http://developer.garmin.com/web-device/garmin-communicator-plugin/">http://developer.garmin.com/web-device/garmin-communicator-plugin/</a></li>
				<li>Weather data from OpenWeatherMap - <a class="external" href="http://openweathermap.org/">http://openweathermap.org</a></li>
			</ul>
		</li>
	</ul>
</div>


<div class="panel-heading panel-sub-heading">
	<h1>Browser Support</h1>
</div>
<div class="panel-content">
	Runalyze is a modern application and therefore we want to use the most modern web techniques.
	We will not try to support any outdated browser versions.

	<ul>
		<li>jQuery 2.x does not support IE8 (or less)</li>
		<li>Flot charts work with IE 6+, Chrome, Firefox 2+, Safari 3+ and Opera 9.5+</li>
	</ul>
</div>