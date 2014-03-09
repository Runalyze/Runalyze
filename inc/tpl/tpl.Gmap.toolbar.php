	<div class="toolbar-box-content">
		<div id="<?php echo $this->StringID; ?>" class="map"></div>

		<div class="toolbar-line">
			<span class="right">
				<span class="link labeledLink" id="map-fullscreen-link" onclick="RunalyzeGMap.toggleFullScreen();">Vollbild</span>

				<span class="link labeledLink" onclick="RunalyzeGMap.zoomIn();"><?php echo Icon::$ZOOM_IN; ?></span>
				<span class="link labeledLink" onclick="RunalyzeGMap.zoomOut();"><?php echo Icon::$ZOOM_OUT; ?></span>
				<span class="link labeledLink" onclick="RunalyzeGMap.autofit();"><?php echo Icon::$ZOOM_FIT; ?></span>
			</span>

			<select id="trainingMapTypeSelect" onchange="RunalyzeGMap.changeMapType($(this).val());">
				<option value="G_NORMAL_MAP">Normal&nbsp;</option>
				<option value="G_HYBRID_MAP">Hybrid&nbsp;</option>
				<option value="G_SATELLITE_MAP">Satellit&nbsp;</option>
				<option value="G_PHYSICAL_MAP">Physikalisch&nbsp;</option>
				<option value="OSM">OpenStreetMap&nbsp;</option>
			</select>

			<label class="checkable" onclick="$(this).children('i').toggleClass('checked');RunalyzeGMap.changeMarkerVisibility();"><i id="trainingMapMarkerVisibile" class="fa fa-fw checkbox-icon <?php if (CONF_TRAINING_MAP_MARKER) echo 'checked'; ?>"></i> Marker anzeigen</label>

			<?php echo Ajax::wrapJSforDocumentReady('$("#trainingMapTypeSelect").val("'.CONF_TRAINING_MAPTYPE.'");'); ?>

			<br class="clear">
		</div>
	</div>