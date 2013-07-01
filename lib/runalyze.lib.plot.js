/*
 * Lib for using Plots in Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var RunalyzePlot = {
			plots: {},
			annotations: {},
			plotSizes: {},
			trainingCharts: {
				options: {
					multiple: true,
					takeMaxWidth: true,
					minWidthForContainer: 450,
					maxWidthForSmallSize: 550,
					fixedWidth: false,
					defaultWidth: 478,
					width: 478
				}
			},

			options: {
				saverReady: false,
				debugging: false,
				cssClassWaiting: 0,
				saveBgColor: "#60A920",
				//saveBgSrc: "/runalyze/img/dash.png",
				saveTitleHeight: 14,
				saveTitleBg: "#1d250e",
				saveTitleFont: "9px Verdana",
				saveTitleColor: "#839276",
				saveGridColor: "#FFF",
				saveGridDefault: "#545454"
			},

			setOptions: function(opt) {
				this.options = $.extend({}, this.options, opt);
				return this;
			},

			clear: function() {
				for (key in this.plots)
					this.remove(key);

				return this;
			},

			initSaver: function() {
				if (!this.options.saverReady) {
					$('body').append(
						'<form style="display:none;" action="call/savePng.php" method="post" target="isavePng" id="savePng">'
							+'<input type="hidden" name="filename" value="Diagramm.png" />'
							+'<input type="hidden" name="image" id="savePng_image" value="" />'
						+'</form><iframe style="display:none;" name="isavePng" src="" width="1" height="1" />');

					this.options.saverReady = true;
				}
			},

			addPlot: function(cssId, data, options) {
				var $e = $("#"+cssId);

				if (cssId in this.plots && $e.length == 0)
					this.remove(cssId);

				if ($e.hasClass('training-chart'))
					$e.width(this.trainingCharts.options.width);

				this.plotSizes[cssId] = $e.width();
				this.plots[cssId] = $.plot(	$e, data, options );

				$e.dblclick(function(){ RunalyzePlot.save(cssId); });

				return this;
			},

			finishInit: function(cssId) {
				this.addPanningArrowsTo(cssId);
			},

			initSettingsLink: function(cssId) {
				$("#"+cssId+" .flot-settings-link").click(function(){
					$("#"+cssId).toggleClass('has-settings-line');
				});
			},

			getPlot: function(idOfPlot) {
				return this.plots[idOfPlot];
			},

			redraw: function(idOfPlot) {
				this.plots[idOfPlot].setupGrid();
				this.plots[idOfPlot].draw();

				return this;
			},

			resize: function(idOfPlot) {
				if (idOfPlot in this.plots && $("#"+idOfPlot).is(":visible")) {
					this.plots[idOfPlot].resize();
					this.plots[idOfPlot].setupGrid();
					this.plots[idOfPlot].draw();
					this.plotSizes[idOfPlot] = $("#"+idOfPlot).width();
				}

				return this;
			},

			resizeAll: function() {
				for (idOfPlot in this.plots)
					this.resize(idOfPlot);

				return this;
			},

			remove: function(idOfPlot) {
				if (idOfPlot in this.plots) {
					this.plots[idOfPlot].shutdown();

					delete this.plots[idOfPlot];
				} else if (this.options.debugging)
					console.log('Unable to remove plot unknown plot: ' + idOfPlot);

				return this;
			},

			addAnnotationTo: function(idOfPlot, x, y, text, toX, toY) {
				var $e = $("#"+idOfPlot),
					k = 'annotation-'+idOfPlot+x.toString().replace('.','')+y.toString().replace('.','');

				this.annotations[k] = {plot: idOfPlot, x: x, y: y, text: text, toX: toX, toY: toY};
				$e.append('<div id="'+k+'" class="annotation">'+text+'</div>');

				return this.positionAnnotation(k);
			},

			positionAnnotation: function(key) {
				var $e = $("#"+key),
					a = this.annotations[key],
					o = this.getPlot(a.plot).pointOffset({'x':a.x, 'y':a.y});

				$e.css({'left':(o.left+a.toX)+'px','top':(o.top+a.toY)+'px'});

				if (o.top+a.toY < 0)
					$e.hide();

				return this;
			},

			repositionAllAnnotations: function() {
				for (key in this.annotations) {
					this.positionAnnotation(key);
				}

				return this;
			},

			enableZoomingFor: function(idOfPlot) {
				$('<div class="arrow" style="right:20px;top:20px">zoom out</div>').appendTo('#'+idOfPlot).click(function (e) {
					e.preventDefault();
					RunalyzePlot.getPlot(idOfPlot).zoomOut();
				});
			},

			resizeTrainingCharts: function() {
				if ($("#training-plots-and-map").length == 0)
					return;

				$("#training-plots-and-map").width( $("#tab_content").width() - $("#training-table").outerWidth(true) - 20 );
				this.trainingCharts.options.width = $("#training-plots").width() - 4;
				this.resizeEachTrainingChart();
				this.repositionAllAnnotations();
			},

			resizeEachTrainingChart: function() {
				$("#training-plots .training-chart").each(function(){
					if ($(this).width() != RunalyzePlot.trainingCharts.options.width) {
						$(this).width(RunalyzePlot.trainingCharts.options.width);
						RunalyzePlot.resize($(this).attr('id'));
					}
				});
			},

			initTrainingNavitation: function() {
				this.resizeTrainingCharts();
				RunalyzeGMap.resize();
			},

			toggleTrainingChart: function(key) {
				var $element = $('#toggle-'+key),
					isActive = $element.hasClass('checked'),
					id = RunalyzePlot.togglerId($element);

				$element.parent().toggleClass('unimportant');

				if (isActive)
					RunalyzePlot.hideTrainingChart(id);
				else
					RunalyzePlot.showTrainingChart(id);

				if (key == "pace") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_PACE", !isActive);
				} else if (key == "pulse") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_PULSE", !isActive);
				} else if (key == "elevation") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_ELEVATION", !isActive);
				} else if (key == "splits") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_SPLITS", !isActive);
				} else if (key == "pacepulse") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_PACEPULSE", !isActive);
				} else if (key == "collection") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_COLLECTION", !isActive);
				} else if (key == "cadence") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_CADENCE", !isActive);
				} else if (key == "power") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_POWER", !isActive);
				} else if (key == "temperature") {
					Runalyze.changeConfig("TRAINING_SHOW_PLOT_TEMPERATURE", !isActive);
				}

				RunalyzePlot.resizeTrainingCharts();
			},

			hideTrainingChart: function(key) {
				$('#toggle-'+key).removeClass('checked');
				$('#plot-'+key).hide();
			},

			showTrainingChart: function(key) {
				$('#toggle-'+key).addClass('checked');
				$('#plot-'+key).show(0, function(){
					if (RunalyzePlot.plotSizes[key] != RunalyzePlot.trainingCharts.options.width)
						RunalyzePlot.resize( $(this).children("div:first").attr("id") );
				});

			},

			togglerId: function(e) {
				return e.attr('id').substr(7);
			},

			toggleSelectionMode: function(idOfPlot) {
				// TODO: Does not work as expected
				var hide = this.getPlot(idOfPlot).getOptions().selection.mode == 'x';
				this.getPlot(idOfPlot).getOptions().selection.mode = hide ? null : 'x';

				if (hide) {
					this.getPlot(idOfPlot).clearSelection();
					$("#"+idOfPlot+" .hoverTip").hide();
				}
			},

			toggleCrosshairMode: function(idOfPlot) {
				this.getPlot(idOfPlot).getOptions().crosshair.mode = this.getPlot(idOfPlot).getOptions().crosshair.mode == 'x' ? null : 'x';
			},

			togglePanning: function(idOfPlot) {
				$("#"+idOfPlot+" .arrow").toggle();
			},

			addPanningArrowsTo: function(idOfPlot) {
				this.addPanningArrow(idOfPlot, 'left', 55, 60, { left: -100 });
				this.addPanningArrow(idOfPlot, 'right', 25, 60, { left: 100 });
				this.addPanningArrow(idOfPlot, 'up', 40, 45, { top: -100 });
				this.addPanningArrow(idOfPlot, 'down', 40, 75, { top: 100 });
			},

			addPanningArrow: function(idOfPlot, dir, right, top, offset) {
				$('<img class="arrow" src="lib/flot-0.8.1/arrow-'+dir+'.gif" style="display:none;right:'+right+'px;top:'+top+'px">').appendTo("#"+idOfPlot).click(function (e) {
					e.preventDefault();
					RunalyzePlot.getPlot(idOfPlot).pan(offset);
				});
			},

			toggleFullscreen: function(idOfPlot) {
				var $e = $("#"+idOfPlot);

				$e.toggleClass('fullscreen');
				$("#"+idOfPlot+" .flot-settings-fullscreen, #"+idOfPlot+" .flot-settings-fullscreen-hide").toggleClass('hide');

				if ($e.hasClass('fullscreen')) {
					$e.attr('data-width', $e.width());
					$e.attr('data-height', $e.height());

					this.setFullscreenSize();
				} else {
					$e.css({
						width: $e.attr('data-width'),
						height: $e.attr('data-height')
					});
				}

				this.resize(idOfPlot);
				this.repositionAllAnnotations();
			},

			setFullscreenSize: function() {
				var $e = $(".flot.fullscreen");

				if ($e.length) {
					$e.css({
						width: $(window).width() - ($e.outerWidth(true) - $e.width()),
						height: $(window).height() - ($e.outerHeight(true) - $e.height())
					});

					this.resize($e.attr('id'));
					this.repositionAllAnnotations();
				}
			},

			save: function(idOfPlot) {
				var obj = this.getPlot(idOfPlot),
					plot = $("#"+idOfPlot+" canvas.flot-base")[0],
					image = document.createElement('canvas');

				this.initSaver();

				if (plot.getContext) {
					//obj.getOptions().canvas = true;
					obj.getOptions().grid.canvasText.show = true; // only used for legend!
					obj.getOptions().grid.color = this.options.saveGridColor;
					RunalyzePlot.redraw(idOfPlot);

					var imgx = image.getContext('2d'),
						$title = $("#"+idOfPlot+" .flotTitle"),
						$annotations = $("#"+idOfPlot+" .annotation"),
						hasTitle = $title.length > 0,
						h = plot.height,
						w = plot.width,
						bg = new Image();

					if (hasTitle)
						h += this.options.saveTitleHeight;

					image.height = h;
					image.width = w;
					imgx.height = h;
					imgx.width = w;

					bg.src = $("#"+idOfPlot).css('background-image').replace(/"/g,"").replace(/url\(|\)$/ig, "");
					bg.onload = function() {
						var pattern = imgx.createPattern(bg, 'repeat');
						imgx.fillStyle = RunalyzePlot.options.saveBgColor;
						imgx.fillRect(0, 0, w, h);
						imgx.fillStyle = pattern;
						imgx.fillRect(0, 0, w, h);
						imgx.drawImage(plot, 0, 0);

						if (hasTitle) {
							imgx.fillStyle = RunalyzePlot.options.saveTitleBg;
							imgx.fillRect(0, h-14, w, h);
							imgx.font = RunalyzePlot.options.saveTitleFont;
							imgx.fillStyle = RunalyzePlot.options.saveTitleColor;
							imgx.fillText($title.children(".left").text(), 6, h-3);
							imgx.textAlign = "right";
							imgx.fillText($title.children(".right").text(), w-6, h-3);
							imgx.textAlign = "center";
							imgx.fillText($title.clone().children().remove().end().text(), Math.round(w/2), h-3);
						}

						var pos, aw, ah;
						$annotations.each(function(){
							pos = $(this).position();
							aw  = $(this).width();
							ah  = $(this).height();

							imgx.fillStyle = $(this).css('background-color');
							imgx.fillRect(pos.left + 2, pos.top + 2, aw, ah);

							imgx.fillStyle = RunalyzePlot.options.saveTitleColor;
							imgx.textAlign = "left";
							imgx.fillText($(this).text(), pos.left + 2, pos.top + ah - 1);
						});

						var imageurl = image.toDataURL("image/png");
						$("#savePng_image").val(imageurl);
						$("#savePng").submit();

						//obj.getOptions().canvas = false;
						obj.getOptions().grid.canvasText.show = false; // only used for legend!
						obj.getOptions().grid.color = RunalyzePlot.options.saveGridDefault;
						RunalyzePlot.redraw(idOfPlot);
					}
				} else {
					window.alert('Sorry, dein Browser ist offensichtlich nicht in der Lage die Diagramme zu speichern.');
				}
			}
	}

	function a(a) {
		return a;
	}

	if (!window.RunalyzePlot)
		window.RunalyzePlot = RunalyzePlot;
})();