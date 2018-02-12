/*
 * Feature
 *
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Feature = (function($, Parent){

	// Public

	var self = {};


	// Private

	var options = {
	};


	// Private Methods

	function initAjaxLinks() {
		$('a.ajax').unbind('click').click(function(e){
			e.preventDefault();

			if ($(this).data('confirm')) {
				if (!window.confirm($(this).data('confirm'))) {
					return false;
				}
			}

			var href = $(this).attr('href');
			var target = $(this).attr('target');

			if (href != '#') {
				if (target == 'statistics-inner') {
					Parent.Statistics.setUrl( href );
				}

				$('#'+target).loadDiv(href, $(this).attr("data-size"));
			}

			return false;
		});
	}

	function initTooltips() {
		$("body > .tooltip").remove();
		$("[rel=tooltip].atLeft").tooltip({animation: true, placement: 'left'});
		$("[rel=tooltip].atRight").tooltip({animation: true, placement: 'right'});
		$("[rel=tooltip]:not(.atLeft):not(.atRight)").tooltip({animation: true});

		return this;
	}

	function initToggle() {
		$(".toggle").unbind("click").click(function(e){
			e.preventDefault();
			$("#"+$(this).attr("rel")).animate({opacity: 'toggle'});
		});
	}

	function initToolbars() {
		$(".toolbar-opener").unbind("click").click(function(){
			$(this).parent().parent().toggleClass('open');
		});
	}

	function initChangeDiv() {
		$("a.change").each(function(){
			if ($("a.change[target="+$(this).attr("target")+"].triggered").length == 0)
				$("a.change[target="+$(this).attr("target")+"]:first").addClass('triggered').parent().addClass('triggered');
			else
				$("a.change[target="+$(this).attr("target")+"].triggered").parent().addClass('triggered');
		}).unbind("click").click(function(e){
			e.preventDefault();

			$(this).closest("li.with-submenu").find("span.link").text($(this).text());

			$("a.change[target="+$(this).attr("target")+"]").removeClass('triggered').parent().removeClass('triggered');
			$(this).addClass('triggered').parent().addClass('triggered');

			var target = "#"+$(this).attr("target");
			var $target = $(target);

			if (target == $(this).attr("href")) {
				var $newDiv = $("#"+ $(this).attr("href").split('#').pop() + " .change");
			} else {
				var $newDiv = $("#"+ $(this).attr("href").split('#').pop());
			}

			var $oldDiv = $(target+" > .change:visible, " + target + " > .panel-content > .change:visible, " + target + " > .statistics-container > .change:visible").not($newDiv);

			$target.addClass('loading');

			var fadeInNewDiv = function() {
				$newDiv.fadeTo( Parent.Options.fadeSpeed(), 1, function(){
					$target.hide().removeClass( Parent.Options.loadingClass() ).fadeIn();
					Parent.createFlot();
					RunalyzePlot.resizeAll();
				});
			};

			if ($oldDiv.length) {
				$oldDiv.fadeOut( Parent.Options.fadeSpeed(), fadeInNewDiv);
			} else {
				fadeInNewDiv();
			}

			return false;
		});
	}

	function initCalendarLink() {
		$('#calendar-link').unbind('click').bind('click', function(){
			var $e = $('#data-browser-calendar');

			$e.toggle();

			if ($e.is(':visible'))
				initCalendar();
		});
	}

	function initCalendar() {
		var $calendar = $('#widget-calendar');

		if ($calendar.text().trim().length > 0)
			return this;

		var dateStart = new Date(parseInt($("#calendar-start").val()));
		var dateEnd = new Date(parseInt($("#calendar-end").val()));

		$calendar.DatePicker({
			flat: true,
			format: 'd B Y',
			date: [new Date(dateStart.getTime() + dateStart.getTimezoneOffset()*60000), new Date(dateEnd.getTime() + dateEnd.getTimezoneOffset()*60000)],
			current: new Date(dateStart.getTime() + dateStart.getTimezoneOffset()*60000),
            locale:  JSON.parse($("#calendar-locale").val()),
			calendars: 3,
            mode: 'range',
			starts: 1
		});

		$('#calendar-submit').unbind('click').bind('click', function(){
			var dates = $calendar.DatePickerGetDate(),
				start = Math.round(dates[0].getTime()/1000 - dates[0].getTimezoneOffset()*60),
				end = Math.round(dates[1].getTime()/1000 - dates[1].getTimezoneOffset()*60);
			$("#data-browser-inner").loadDiv('call/call.DataBrowser.display.php?start='+start+'&end='+end);
		});

		return this;
	}

	function initFormulars() {
		initFormularElements();
		initFormularSubmit();
	}

	function initFormularElements() {
		var config = {
			'.chosen-select-create': {width: "auto", create_option: true, skip_no_results: true, create_option_text:"Add a tag"},
			'.chosen-select': {width: "auto", no_results_text: "No results match"}
		};

		for (var selector in config) {
			$(selector).chosen(config[selector]);
		}

		$(".chosen-select-all").unbind('click').bind('click', function() {
			var target = $(this).data('target');
			$("#"+target+" option").prop('selected', true);
			$("#"+target).change().trigger('chosen:updated');
		});
		$(".chosen-select-none").unbind('click').bind('click', function() {
			var target = $(this).data('target');
			$("#"+target+" option").prop('selected', false);
			$("#"+target).change().trigger('chosen:updated');
		});

		$(".fip-select").fontIconPicker({emptyIcon: false, hasSearch: false});
		$(".pick-a-date:not(.has-a-datepicker)").each(function(){
			var $t = $(this);
			var $locale = $("#calendar-locale");
			var options = {
                format: $(this).data('format') || 'd.m.Y',
                date: $t.val(),
                current: $t.val(),
                calendars: 1,
                starts: 1,
                position: 'bottom',
                mode: 'single',
                onBeforeShow: function(){ if ($t.val().trim() != '') $t.DatePickerSetDate($t.val(), true); },
                onChange: function(formated, dates){ $t.val(formated); $t.trigger('change'); $t.DatePickerHide(); }
            };

			if ($locale && $locale.val()) {
				options.locale = JSON.parse($locale.val());
			}

            $t.addClass('has-a-datepicker');

			$t.DatePicker(options);
		});

		$('form label, form .checkbox-collection > div').each(function(){
			if (this.offsetWidth < this.scrollWidth) {
				$(this).attr('title', $(this).text().trim()).tooltip({animation: true});
			}
		});
	}

	function initFormularSubmit() {
		// Warning: Does only work for formulars in #ajax
		$("#ajax").find("form.ajax").unbind("submit").submit(function(e){
			e.preventDefault();

			if ($(this).children(":submit").hasClass('debug')) {
				window.alert($(this).serialize());
				return false;
			}

			if ($(this).data('confirm')) {
				if (!window.confirm($(this).data('confirm'))) {
					return false;
				}
			}

			var formID = $(this).attr("id");
			var noreload = $(this).hasClass('no-automatic-reload');
			var data = $(this).serializeArray();
			var url = $(this).attr('action');
			var elem = $("#ajax");

			data.push({
				name:	'submit',
				value:	'submit'
			});

			if (formID == "search" && $("form.ajax input[name=send-to-multi-editor]:checked").length == 0) {
				$("#searchResult").loadDiv(url+'?pager=true', data, {success: function(){
					$('#searchResult').find('.submenu .link').unbind('click').bind('click', function(){
						var element = $(this);
						if (element.data('action') == 'delete') {
							Parent.Training.deleteActivity(element.data('activityid'), element.data('confirm'), {
								success: function() {
									element.closest('tr').remove();
								}
							});
						} else if (element.data('action') == 'privacy') {
							Parent.Training.changePrivacyOfActivity(element.data('activityid'), {
								success: function() {
									$('#search').submit();
								}
							});
						}
					});
				}});
				return false;
			}

			$("body > .datepicker").remove();

			if ($("#pluginTool").length) {
				elem = $("#pluginTool");
			}

			elem.loadDiv( url, data, {
				success: function() {
					$("#submit-info").fadeIn().delay(4000).fadeOut();

					if (formID != "search" && formID != "tcxUpload" && !noreload)
						Parent.reloadContent();
				}
			} );

			return false;
		});
	}

	function initRelativeTimes() {
		$('time.timeago').timeago();
	}


	// Public Methods

	self.init = function() {
		initAjaxLinks();
		initTooltips();
		initToggle();
		initToolbars();
		initChangeDiv();
		initCalendarLink();
		initFormulars();
		initRelativeTimes();
	};

	self.initToggle = function() {
		initToggle();
	};

	self.radialProgress = function(element, scoreInPercent, scoreAsValue) {
        element.find('.inset').text(scoreAsValue.toFixed(1));

        var transform_styles = ['-webkit-transform', '-ms-transform', 'transform'];
        var mask = element.find('.fill, .mask.full');
        var fix = element.find('.fill.fix');

        for (var i in transform_styles) {
            mask.css(transform_styles[i], 'rotate(' + (scoreInPercent * 180) + 'deg)');
            fix.css(transform_styles[i], 'rotate(' + (scoreInPercent * 360) + 'deg)');
        }
	};

	Parent.addLoadHook('init-feature', self.init);

	return self;
})(jQuery, Runalyze);
