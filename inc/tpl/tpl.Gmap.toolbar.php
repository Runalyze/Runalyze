<div id="trainingMap" class="toolbar asBox open" id="trainingGmapToolbar">

	<div class="toolbar-box-content">
		<div id="<?php echo $this->StringID; ?>" class="map"></div>
	</div>

	<div class="toolbar-content toolbar-line">
		<span class="right">
			<span class="link labeledLink zoomInLink" onclick="RunalyzeGMap.zoomIn();"></span>
			<span class="link labeledLink zoomOutLink" onclick="RunalyzeGMap.zoomOut();"></span>
			<span class="link labeledLink zoomFitLink" onclick="RunalyzeGMap.autofit();"></span>
			<!-- TODO: Fullsize -->
		</span>

		<select id="trainingMapTypeSelect" onchange="RunalyzeGMap.changeMapType($(this).val());">
			<option value="G_NORMAL_MAP">Normal&nbsp;</option>
			<option value="G_HYBRID_MAP">Hybrid&nbsp;</option>
			<option value="G_SATELLITE_MAP">Satellit&nbsp;</option>
			<option value="G_PHYSICAL_MAP">Physikalisch&nbsp;</option>
			<option value="OSM">OpenStreetMap&nbsp;</option>
		</select>

		<label class="checkable" onclick="$(this).children('i').toggleClass('checked');RunalyzeGMap.changeMarkerVisibility();"><i id="trainingMapMarkerVisibile" class="checkbox-icon <?php if (CONF_TRAINING_MAP_MARKER) echo 'checked'; ?>"></i> Marker anzeigen</label>

		<?php echo Ajax::wrapJSforDocumentReady('$("#trainingMapTypeSelect").val("'.CONF_TRAINING_MAPTYPE.'");'); ?>

		<br class="clear" />
	</div>
	<div class="toolbar-nav nomargin">
		<div class="toolbar-opener" style=""></div>
	</div>
</div>