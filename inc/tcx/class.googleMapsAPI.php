<?php
function getJavascriptDecimalNumber($value, $rounding=3) {
	return number_format($value, $rounding, ".", "");
}

/*
 * Diese Klasse enthaelt saemtliche Google Maps API Funktionen
 */
class googleMapsAPI {
	var $api_key = "";
	var $map_id = "";
	var $width = "500px";
	var $height = "500px";
	var $map_type = "G_MAP_TYPE";
	var $markers = array();
	var $polylines = array();
	var $max_lon = -1000000;
	var $min_lon = 1000000;
	var $max_lat = -1000000;
	var $min_lat = 1000000;
	var $center_lat = 46.77429;
	var $center_lon = 7.57164;
	var $start_filter = 1;
	var $panzoomcontrol = "GLargeMapControl";
	var $maptypecontrol = "GMapTypeControl";
	var $overviewmap = "";
	var $scalecontrol = "";
	var $zoom = 0;
	var $deltazoom = 0;
	var $maptypecontrolprops = array();
	var $dragging = "enable";
	var $doubleclickzoom = "enable";
	var $continuouszoom = "enable";
	var $scrollwheelzoom = "enable";
	var $googlebar = "disable";
	var $icons = array();
	var $fullscreen = 0;


	function __construct($api_key = '', $map_type="map") {
		//Default Values
		$this->map_id = "map".md5(uniqid(rand()));
		$this->setMapTypeControlButton("G_NORMAL_MAP", "show");
		$this->setMapTypeControlButton("G_HYBRID_MAP", "show");
		$this->setMapTypeControlButton("G_SATELLITE_MAP", "show");
		$this->setMapTypeControlButton("G_PHYSICAL_MAP", "hide");

		$this->api_key = $api_key;
		$this->setMapType($map_type);
	}
	
	function setFullscreen($value) {
		$this->fullscreen = $value;
	}

	function setDragging($value) {
		switch (strtolower($value)) {
			case "disabled":
				$this->dragging = "disable";
				break;
			default:
				$this->dragging = "enable";
				break;
		}
	}

	function setDoubleClickZoom($value) {
		switch (strtolower($value)) {
			case "disabled":
				$this->doubleclickzoom = "disable";
				break;
			default:
				$this->doubleclickzoom = "enable";
				break;
		}
	}

	function setContinuousZoom($value) {
		switch (strtolower($value)) {
			case "disabled":
				$this->continuouszoom = "disable";
				break;
			default:
				$this->continuouszoom = "enable";
				break;
		}
	}

	function setScrollWheelZoom($value) {
		switch (strtolower($value)) {
			case "disabled":
				$this->scrollwheelzoom = "disable";
				break;
			default:
				$this->scrollwheelzoom = "enable";
				break;
		}
	}

	function setGoogleBar($value) {
		switch (strtolower($value)) {
			case "enabled":
				$this->googlebar = "enable";
				break;
			default:
				$this->googlebar = "disable";
				break;
		}
	}

	function setPanZoomControl($value){
		switch (strtolower($value)) {
			case "hide":
				$this->panzoomcontrol = "";
				break;
			case "small":
				$this->panzoomcontrol = "GSmallMapControl";
				break;
			case "zoom":
				$this->panzoomcontrol = "GSmallZoomControl";
				break;
			case "zoom3d":
				$this->panzoomcontrol = "GSmallZoomControl3D";
				break;				
			case "large":
				$this->panzoomcontrol = "GLargeMapControl";
				break;				
			case "large3d":
			default:
				$this->panzoomcontrol = "GLargeMapControl3D";
				break;
		}
	}

	function setOverviewMap($value) {
		switch (strtolower($value)) {
			case "show":
				$this->overviewmap = "GOverviewMapControl";
				break;
			default:
				$this->overviewmap = "";
				break;
		}
	}

	function setZoom($value) {
		$this->zoom = $value;
	}

	function setDeltaZoom($value) {
		$this->deltazoom = $value;
	}

	function setScaleControl($value) {
		switch (strtolower($value)) {
			case "show":
				$this->scalecontrol = "GScaleControl";
				break;
			default:
				$this->scalecontrol = "";
				break;
		}
	}

	function setMapTypeControl($value) {
		switch (strtolower($value)) {
			case "hide":
				$this->maptypecontrol = "";
				break;
			case "hierarchical":
				$this->maptypecontrol = "GHierarchicalMapTypeControl";
				break;
			default:
				$this->maptypecontrol = "GMapTypeControl";
				break;
		}
	}

	function setMapTypeControlButton($maptype,$visible) {
		$this->maptypecontrolprops[$maptype]["name"] = $maptype;
		switch (strtolower($visible)) {
			case "hide":
				$this->maptypecontrolprops[$maptype]["visible"] = 0;
				break;
			default:
				$this->maptypecontrolprops[$maptype]["visible"] = 1;
				break;
		}
	}

	function setWidth($width) {
		if (!preg_match('!^(\d+)(.*)$!', $width, $_match)) {
			return false;
		}

		$_width = $_match[1];
		$_type = $_match[2];
		if ($_type == '%') {
			$this->width = $_width . '%';
		}
		else {
			$this->width = $_width . 'px';
		}

		return true;
	}

	function setStartFilter($filter) {
		if (is_numeric($filter)){
			if ($filter > 0) {
				$this->start_filter=$filter;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function setHeight($height) {
		if (!preg_match('!^(\d+)(.*)$!', $height,$_match)) {
			return false;
		}

		$_height = $_match[1];
		$_type = $_match[2];
		if ($_type == '%') {
			$this->height = $_height . '%';
		}
		else {
			$this->height = $_height . 'px';
		}

		return true;
	}

	function setSize($width, $height) {
		$this->setWidth($width);
		$this->setHeight($height);
	}

	function setMapType($type) {
		switch (strtoupper($type)) {
			case 'G_HYBRID_MAP':
			case 'G_HYBRID_TYPE':
			case 'HYBRID':
				$this->map_type = 'G_HYBRID_MAP';
				break;
			case 'G_SATELLITE_MAP':
			case 'G_SATELLITE_TYPE':
			case 'SATELLITE':
				$this->map_type = 'G_SATELLITE_MAP';
				break;
			case 'G_PHYSICAL_MAP':
			case 'G_PHYSICAL_TYPE':
			case 'PHYSICAL':
				$this->map_type = 'G_PHYSICAL_MAP';
				break;
			case 'G_SATELLITE_3D_MAP':
			case 'G_SATELLITE_3D_TYPE':
			case 'EARTH':
			case '3D_SATELLITE':
				$this->map_type = 'G_SATELLITE_3D_MAP';
				break;
			case 'G_NORMAL_MAP':
			case 'G_MAP_TYPE':
			case 'MAP':
			case 'NORMAL':
			default:
				$this->map_type = 'G_NORMAL_MAP';
				break;
		}
		$this->setMapTypeControlButton($this->map_type,"show");
	}

	function clear() {
		$this->markers = array();
		$this->polylines = array();
		$this->max_lon = -1000000;
		$this->min_lon = 1000000;
		$this->max_lat = -1000000;
		$this->min_lat = 1000000;
		$this->center_lat = 46.77429;
		$this->center_lon = 7.57164;
		$this->zoom = 0;
	}

	function addMarker($lat, $lon, $html = '', $url='', $urlaction='dblclick', $iconImage = '', $iconShadowImage = '', $iconImageSizeX=6, $iconImageSizeY=10, $iconShadowSizeX=11, $iconShadowSizeY=10, $iconAnchorX=3, $iconAnchorY=9) {
		$html = str_replace('"',"'", $html);
		$html = str_replace("\n","", $html);
		$html = str_replace("\r","", $html);
		$marker['lon'] = $lon;
		$marker['lat'] = $lat;
		$marker['html'] = $html;
		$marker['url'] = $url;
		if (strlen($url)==0) {
			$marker['urlaction'] = "disabled";
		} else {
			$marker['urlaction'] = $urlaction;
		}
		if (strlen($iconImage)>0) {
			$key = $iconImage.$iconShadowImage;
			$marker['iconkey'] = $key;
			if (intval(array_key_exists($key,$this->icons))==0) {
				$this->icons[$key]['id'] = count($this->icons);
			}
			$marker['iconid'] = $this->icons[$key]['id'];
			$this->icons[$key]['image'] = $iconImage;
			$this->icons[$key]['imagesizex'] = $iconImageSizeX;
			$this->icons[$key]['imagesizey'] = $iconImageSizeY;
			$this->icons[$key]['shadow'] = $iconShadowImage;
			$this->icons[$key]['shadowsizex'] = $iconShadowSizeX;
			$this->icons[$key]['shadowsizey'] = $iconShadowSizeY;
			$this->icons[$key]['anchorx'] = $iconAnchorX;
			$this->icons[$key]['anchory'] = $iconAnchorY;
		}
		$this->markers[] = $marker;
		$this->adjustCenterCoords($marker['lon'], $marker['lat']);
		return count($this->markers) - 1;
	}

	function addPolyline($color = '', $weight = 0, $opacity = 0) {
		$polyline["color"] = $color;
		$polyline["weight"] = $weight;
		$polyline["opacity"] = $opacity;
		$this->polylines[] = $polyline;
		return count($this->polylines) - 1;
	}

	function addPolylinePoint($polyline,$lat,$lon)
	{
		$pt["lat"] = $lat;
		$pt["lon"] = $lon;
		$this->polylines[$polyline]["points"][] = $pt;
		$this->adjustCenterCoords($pt['lon'], $pt['lat']);
		return count($this->polylines[$polyline]["points"]) - 1;
	}

	function adjustCenterCoords($lon, $lat) {
		if (strlen((string)$lon) == 0 || strlen((string)$lat) == 0) {
			return false;
		}
		$this->max_lon = max($lon, $this->max_lon);
		$this->min_lon = min($lon, $this->min_lon);
		$this->max_lat = max($lat, $this->max_lat);
		$this->min_lat = min($lat, $this->min_lat);
		$this->center_lon = ($this->min_lon + $this->max_lon) / 2;
		$this->center_lat = ($this->min_lat + $this->max_lat) / 2;
		return true;
	}

	function clearCenterCoords() {
		$this->max_lon = -1000000;
		$this->min_lon = 1000000;
		$this->max_lat = -1000000;
		$this->min_lat = 1000000;
		$this->center_lat = 46.77429;
		$this->center_lon = 7.57164;
	}

	function getHeaderScript() {
		$ret .= $this->addLine('<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=%s" type="text/javascript"></script>');
		return sprintf($ret, $this->api_key);
	}

	function getKMLContentElementOnly($url) {
		$kmlname = $this->map_id."geoXml";
		//Div
		$ret .= $this->getCommon_FirstLines();
		//Initializing
		$ret .= $this->addLine(sprintf('var %s = null;',$this->map_id),2);
		$ret .= $this->addLine(sprintf('var %s = null;',$kmlname),2);
		$ret .= $this->addLine(sprintf('var cb_geo_listener_%s = null;',$this->map_id),2);

		//Workaraound for Showing other MapTypes than MAP

		$ret .= $this->getCommon_SetMapType();

		//Callback after GeoLoading
		$ret .= $this->addLine(sprintf('var cb_geo_%s = function() {', $this->map_id),2);
		$ret .= $this->addLine(sprintf('GEvent.removeListener(cb_geo_listener_%s);',$this->map_id),3);
		$ret .= $this->addLine(sprintf('try {',$kmlname),3);
		if ($this->max_lon > -1000000) {
			if ($this->zoom == 0) {
				$ret .= $this->addLine(sprintf('var zoom = %s.getBoundsZoomLevel(new GLatLngBounds(new GLatLng(%s,%s),new GLatLng(%s,%s)));',$this->map_id,$this->min_lat,$this->min_lon,$this->max_lat,$this->max_lon),4);
				$ret .= $this->addLine(sprintf('%s.setCenter(new GLatLng(%s,%s), zoom + %s);',$this->map_id, $this->center_lat,$this->center_lon, $this->deltazoom),4);
			} else {
				$ret .= $this->addLine(sprintf('%s.setCenter(new GLatLng(%s,%s), %s);',$this->map_id, $this->center_lat,$this->center_lon, $this->zoom),4);
			}
		} else {
			if ($this->zoom == 0) {
				$ret .= $this->addLine(sprintf('%s.gotoDefaultViewport(%s);',$kmlname,$this->map_id),4);
				if ($this->deltazoom != 0) {
					$ret .= $this->addLine(sprintf('%s.setZoom(%s.getZoom() + %s);',$this->map_id, $this->map_id, $this->deltazoom),4);
				}
			} else {
				$ret .= $this->addLine(sprintf('%s.setCenter(%s.getDefaultCenter(),%s);',$this->map_id,$kmlname,$this->zoom),4);
			}
		}

		$ret .= $this->getCommon_Controls_AddRemMapType_Behaviours(4);

		$ret .= $this->getCommon_MarkersIcons(4);

		$ret .= $this->addLine(sprintf('if (%s.hasLoaded()) {',$kmlname),4);
		$ret .= $this->addLine(sprintf('%s.addOverlay(%s);', $this->map_id, $kmlname),5);
		$ret .= $this->addLine(sprintf('} else {'),4);
		$ret .= $this->addLine(sprintf('throw("GGeoXML Object not loaded properly!");'),5);
		$ret .= $this->addLine(sprintf('}'),4);
		$ret .= $this->addLine(sprintf('setTimeout(smt_%s, 100);', $this->map_id, $this->map_id),4);						
		$ret .= $this->addLine('} catch(err) {',3);
		$ret .= $this->addLine(sprintf('document.write("Could not load the file with the GGeoXML Object! Check the file, the Google Maps API says that there is an error! Validate your links or files with the Validators from <a href=\'http://googlegeodevelopers.blogspot.com/2008/06/new-service-released-kml-validator.html\' target=\'_blank\'>http://googlegeodevelopers.blogspot.com/2008/06/new-service-released-kml-validator.html</a> or try enter the URL to your file directly in the search field of <a href=\'http://maps.google.com\' target=\'_blank\'>http://maps.google.com</a>!<br //><br //>'.$url.'");'),4);
		$ret .= $this->addLine('}',3);
		$ret .= $this->addLine('}',2);

		//Loading
		$url = str_replace("&amp;","&",$url);
		$ret .= $this->addLine(sprintf('function load_%s() {', $this->map_id),2);
		$ret .= $this->addLine(sprintf('%s = new GMap2(document.getElementById("%s"));',$this->map_id,$this->map_id),3);
		$ret .= $this->addLine(sprintf('%s = new GGeoXml("%s");',$kmlname ,$url),3);
		$ret .= $this->addLine(sprintf('cb_geo_listener_%s = GEvent.addListener(%s, "load", cb_geo_%s);', $this->map_id, $kmlname , $this->map_id),3);
		
		$ret .= $this->addLine('}',2);

		//Running
		$ret .= $this->getCommon_Init();

		$ret .= $this->getCommon_LastLines();

		return $ret;
	}

	function getContentElement() {
		$ret .= $this->getCommon_FirstLines();
		$ret .= $this->addLine(sprintf('var %s = null;',$this->map_id),2);

		$ret .= $this->getCommon_SetMapType();

		//Loading
		$ret .= $this->addLine(sprintf('function load_%s() {', $this->map_id),2);
		$ret .= $this->addLine(sprintf('%s = new GMap2(document.getElementById("%s"));',$this->map_id,$this->map_id),3);
		if ($this->zoom <= 0) {
			$ret .= $this->addLine(sprintf('var zoom = %s.getBoundsZoomLevel(new GLatLngBounds(new GLatLng(%s,%s),new GLatLng(%s,%s)));',$this->map_id,$this->min_lat,$this->min_lon,$this->max_lat,$this->max_lon),3);
			$ret .= $this->addLine(sprintf('%s.setCenter(new GLatLng(%s,%s), zoom + %s);',$this->map_id, $this->center_lat,$this->center_lon, $this->deltazoom),3);
		} else {
			$ret .= $this->addLine(sprintf('%s.setCenter(new GLatLng(%s,%s), %s);',$this->map_id, $this->center_lat,$this->center_lon, $this->zoom),3);
		}

		$ret .= $this->getCommon_Controls_AddRemMapType_Behaviours();
		
		$ret .= $this->getCommon_MarkersIcons();

		$ret .= $this->getCommon_Polylines();

		$ret .= $this->addLine(sprintf('setTimeout(smt_%s, 100);', $this->map_id, $this->map_id),3);
		$ret .= $this->addLine('}',2);

		//Running
		$ret .= $this->getCommon_Init();

		$ret .= $this->getCommon_LastLines();
		return $ret;
	}

	function getCommon_Controls_AddRemMapType_Behaviours($level=3) {
		$firstmaptype = $this->map_type;
		$ret .= $this->addLine(sprintf('%s.addMapType(%s);',$this->map_id,$firstmaptype),$level);
		foreach ($this->maptypecontrolprops as $maptypeprop) {
			if (strtolower($firstmaptype) != strtolower($maptypeprop["name"])) {
				if ($maptypeprop["visible"] == 1) {
					$ret .= $this->addLine(sprintf('%s.addMapType(%s);',$this->map_id,$maptypeprop["name"]),$level);
				} else {
					$ret .= $this->addLine(sprintf('%s.removeMapType(%s);',$this->map_id,$maptypeprop["name"]),$level);
				}
			}
		}

		if (strlen($this->panzoomcontrol)>0) {
		 $ret .= $this->addLine(sprintf('%s.addControl(new %s());',$this->map_id,$this->panzoomcontrol),$level);
		}
		if (strlen($this->overviewmap)>0) {
		 $ret .= $this->addLine(sprintf('%s.addControl(new %s());',$this->map_id,$this->overviewmap),$level);
		}
		if (strlen($this->scalecontrol)>0) {
		 $ret .= $this->addLine(sprintf('%s.addControl(new %s());',$this->map_id,$this->scalecontrol),$level);
		}
		if (strlen($this->maptypecontrol)>0) {
		 $ret .= $this->addLine(sprintf('%s.addControl(new %s());',$this->map_id,$this->maptypecontrol),$level);
		}
		
		#$ret .= $this->addLine(sprintf('%s.addControl(new BannerButtonControl());',$this->map_id,$this->panzoomcontrol),$level);
		#if ($this->fullscreen != 1) {
		#	$ret .= $this->addLine(sprintf('%s.addControl(new FullScreenButtonControl());',$this->map_id,$this->panzoomcontrol),$level);
		#}

		$ret .= $this->addLine(sprintf('%s.%sDragging();',$this->map_id,$this->dragging),$level);
		$ret .= $this->addLine(sprintf('%s.%sDoubleClickZoom();',$this->map_id,$this->doubleclickzoom),$level);
		$ret .= $this->addLine(sprintf('%s.%sContinuousZoom();',$this->map_id,$this->continuouszoom),$level);
		$ret .= $this->addLine(sprintf('%s.%sGoogleBar();',$this->map_id,$this->googlebar),$level);
		$ret .= $this->addLine(sprintf('%s.%sScrollWheelZoom();',$this->map_id,$this->scrollwheelzoom),$level);
		return $ret;
	}

	function getCommon_Init() {
		//Running
		$ret .= $this->addLine('if (GBrowserIsCompatible()) {',2);
		$ret .= $this->addLine(sprintf('setTimeout(load_%s,100);', $this->map_id),3);
		$ret .= $this->addLine('} else {',2);
		$ret .= $this->addLine('document.write("Javascript must be enabled in order to use Google Maps.");',3);
		$ret .= $this->addLine('}',2);
		return $ret;
	}

	function getCommon_SetMapType() {
		//Workaraound for Showing other MapTypes than MAP
		$ret .= $this->addLine(sprintf('var smt_%s = function() {', $this->map_id),2);
		$ret .= $this->addLine(sprintf('%s.setMapType(%s);',$this->map_id,$this->map_type),3);
		$ret .= $this->addLine('}',2);
		return $ret;
	}

	function getCommon_FirstLines() {
		$ret .= $this->addLine(sprintf('<div id="%s" style="width: %s; height: %s;"></div>',$this->map_id, $this->width, $this->height));
		$ret .= $this->addLine('<script type="text/javascript">');
		$ret .= $this->addLine('<!--',1);
		return $ret;
	}

	function getCommon_LastLines() {
		$ret .= $this->addLine('// -->',1);
		$ret .= $this->addLine('</script>');
		return $ret;
	}

	function getCommon_MarkersIcons($level=3) {
		foreach ($this->icons as $icon) {
			$ret .= $this->addLine(sprintf('var %s_icon%s = new GIcon(G_DEFAULT_ICON);',$this->map_id, $icon['id']),$level);
			$ret .= $this->addLine(sprintf('%s_icon%s.image = "%s";',$this->map_id, $icon['id'], $icon['image']),$level);
			$ret .= $this->addLine(sprintf('%s_icon%s.iconSize = new GSize(%s, %s);',$this->map_id, $icon['id'], $icon['imagesizex'], $icon['imagesizey']),$level);
			$ret .= $this->addLine(sprintf('%s_icon%s.iconAnchor = new GPoint(%s, %s);',$this->map_id, $icon['id'], $icon['anchorx'], $icon['anchory']),$level);
			if (strlen($icon['shadow'])>0) {
				$ret .= $this->addLine(sprintf('%s_icon%s.shadow = "%s";',$this->map_id, $icon['id'], $icon['shadow']),$level);
				$ret .= $this->addLine(sprintf('%s_icon%s.shadowSize = new GSize(%s, %s);',$this->map_id, $icon['id'], $icon['shadowsizex'], $icon['shadowsizey']),$level);
			}
		}

		$markerct = 0;
		foreach($this->markers as $marker) {			
			if (strlen($marker['iconid'])>0) {
				$ret .= $this->addLine(sprintf('var %s_marker%s = new GMarker(new GLatLng(%s,%s), {icon:%s_icon%s} );',$this->map_id, $markerct, $marker["lat"], $marker["lon"], $this->map_id, $marker['iconid']),$level);
			} else {
				$ret .= $this->addLine(sprintf('var %s_marker%s = new GMarker(new GLatLng(%s,%s));',$this->map_id, $markerct, $marker["lat"], $marker["lon"]),$level);
			}
			$ret .= $this->addLine(sprintf('%s.addOverlay(%s_marker%s);',$this->map_id, $this->map_id,$markerct),$level);
			if (strlen($marker["html"])>0) {
				$ret .= $this->addLine(sprintf('%s_marker%s.bindInfoWindowHtml("%s");', $this->map_id, $markerct, $marker["html"]),$level);
			}
			if ((strtolower($marker["urlaction"]) != "disabled") && (strlen($marker["url"])>0)) {
				$ret .= $this->addLine(sprintf('GEvent.addListener(%s_marker%s, "%s", function() { window.open("%s","_blank"); });', $this->map_id,$markerct,$marker["urlaction"],$marker["url"]),$level);
			}		
			$markerct++;
		}
		return $ret;
	}

	function getCommon_Polylines() {
		foreach($this->polylines as $polyline) {
			$pointcol = null;
			$pointcol = array();
			$pointcol_i = 0;
			$point_i=0;
			foreach($polyline["points"] as $point) {
				$pointcol[$pointcol_i][$point_i][0] = $point["lat"];
				$pointcol[$pointcol_i][$point_i][1] = $point["lon"];
				$point_i++;
				if ($point_i>=400) {
					$pointcol_i++;
					$point_i=0;
					$pointcol[$pointcol_i][$point_i][0] = $point["lat"];
					$pointcol[$pointcol_i][$point_i][1] = $point["lon"];
					$point_i++;
				}
			}
			$enc = new xmlgooglemaps_googleMapAPIPolylineEnc(32,4);
			for ($i=0; $i<count($pointcol);$i++) {
				$encarr = $enc->dpEncode($pointcol[$i]);
				$ret .= $this->addLine(sprintf('%s.addOverlay(new GPolyline.fromEncoded({color: "%s", weight: %s, opacity: %s, points: "%s", levels: "%s", zoomFactor: %s, numLevels: %s}));',$this->map_id,$polyline["color"], $polyline["weight"], getJavascriptDecimalNumber($polyline["opacity"]/100),$encarr[2], $encarr[1], 32, 4),3);
			}
		}
		return $ret;
	}

	function addLine($text='', $indent = 0) {
		$ret = "";
		for ($i=0;$i<$indent;$i++) {
			$ret .= "   ";
		}
		return $ret.$text."\n";
	}

}

// Klasse zum Berechnen des Encodings einer Polyline
class xmlgooglemaps_googleMapAPIPolylineEnc {
	var $numLevels = 18;
	var $zoomFactor = 2;
	var $verySmall = 0.00001;
	var $forceEndpoints = true;
	var $zoomLevelBreaks = array();

	function xmlgooglemaps_googleMapAPIPolylineEnc($zoomfactor, $numlevels) {
		$this->numLevels = $numlevels;
		$this->zoomFactor = $zoomfactor;
		for($i = 0; $i < $this->numLevels; $i++)
		{
			$this->zoomLevelBreaks[$i] = $this->verySmall*pow($this->zoomFactor, $this->numLevels-$i-1);
		}
	}

	function computeLevel($dd)
	{
		if($dd > $this->verySmall)
		{
			$lev = 0;
			while($dd < $this->zoomLevelBreaks[$lev])
			{
				$lev++;
			}
		}
		return $lev;
	}

	function dpEncode($points)
	{
		if(count($points) > 2)
		{
			$stack[] = array(0, count($points)-1);
			while(count($stack) > 0)
			{
				$current = array_pop($stack);
				$maxDist = 0;
				for($i = $current[0]+1; $i < $current[1]; $i++)
				{
					$temp = $this->distance($points[$i], $points[$current[0]], $points[$current[1]]);
					if($temp > $maxDist)
					{
						$maxDist = $temp;
						$maxLoc = $i;
						if($maxDist > $absMaxDist)
						{
							$absMaxDist = $maxDist;
						}
					}
				}
				if($maxDist > $this->verySmall)
				{
					$dists[$maxLoc] = $maxDist;
					array_push($stack, array($current[0], $maxLoc));
					array_push($stack, array($maxLoc, $current[1]));
				}
			}
		}

		$encodedPoints = $this->createEncodings($points, $dists);
		$encodedLevels = $this->encodeLevels($points, $dists, $absMaxDist);
		$encodedPointsLiteral = str_replace('\\',"\\\\",$encodedPoints);

		return array($encodedPoints, $encodedLevels, $encodedPointsLiteral);
	}

	function distance($p0, $p1, $p2)
	{
		if($p1[0] == $p2[0] && $p1[1] == $p2[1])
		{
			$out = sqrt(pow($p2[0]-$p0[0],2) + pow($p2[1]-$p0[1],2));
		}
		else
		{
			$u = (($p0[0]-$p1[0])*($p2[0]-$p1[0]) + ($p0[1]-$p1[1]) * ($p2[1]-$p1[1])) / (pow($p2[0]-$p1[0],2) + pow($p2[1]-$p1[1],2));
			if($u <= 0)
			{
				$out = sqrt(pow($p0[0] - $p1[0],2) + pow($p0[1] - $p1[1],2));
			}
			if($u >= 1)
			{
				$out = sqrt(pow($p0[0] - $p2[0],2) + pow($p0[1] - $p2[1],2));
			}
			if(0 < $u && $u < 1)
			{
				$out = sqrt(pow($p0[0]-$p1[0]-$u*($p2[0]-$p1[0]),2) + pow($p0[1]-$p1[1]-$u*($p2[1]-$p1[1]),2));
			}
		}
		return $out;
	}

	function encodeSignedNumber($num)
	{
		$sgn_num = $num << 1;
		if ($num < 0)
		{
			$sgn_num = ~($sgn_num);
		}
		return $this->encodeNumber($sgn_num);
	}

	function createEncodings($points, $dists)
	{
		for($i=0; $i<count($points); $i++)
		{
			if(isset($dists[$i]) || $i == 0 || $i == count($points)-1)
			{
				$point = $points[$i];
				$lat = $point[0];
				$lng = $point[1];
				$late5 = floor($lat * 1e5);
				$lnge5 = floor($lng * 1e5);
				$dlat = $late5 - $plat;
				$dlng = $lnge5 - $plng;
				$plat = $late5;
				$plng = $lnge5;
				$encoded_points .= $this->encodeSignedNumber($dlat) . $this->encodeSignedNumber($dlng);
			}
		}
		return $encoded_points;
	}

	function encodeLevels($points, $dists, $absMaxDist)
	{
		if($this->forceEndpoints)
		{
			$encoded_levels .= $this->encodeNumber($this->numLevels-1);
		}
		else
		{
			$encoded_levels .= $this->encodeNumber($this->numLevels-$this->computeLevel($absMaxDist)-1);
		}
		for($i=1; $i<count($points)-1; $i++)
		{
			if(isset($dists[$i]))
			{
				$encoded_levels .= $this->encodeNumber($this->numLevels-$this->computeLevel($dists[$i])-1);
			}
		}
		if($this->forceEndpoints)
		{
			$encoded_levels .= $this->encodeNumber($this->numLevels -1);
		}
		else
		{
			$encoded_levels .= $this->encodeNumber($this->numLevels-$this->computeLevel($absMaxDist)-1);
		}
		return $encoded_levels;
	}

	function encodeNumber($num)
	{
		while($num >= 0x20)
		{
			$nextValue = (0x20 | ($num & 0x1f)) + 63;
			$encodeString .= chr($nextValue);
			$num >>= 5;
		}
		$finalValue = $num + 63;
		$encodeString .= chr($finalValue);
		return $encodeString;
	}
}


?>