/*
 * Lib for logging errors in Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var RunalyzeLog = {
			errorTypes: ["TODO", "INFO", "DEBUG", "NOTICE", "ERROR", "WARNING"],
			$errorTable: null,
			iterator: 0,

			options: {
				containerId: "error-toolbar",
				hideEmptyLog: true
			},

			setOptions: function(opt) {
				this.options = $.extend({}, this.options, opt);
				return this;
			},

			checkVisibility: function() {
				if (this.options.hideEmptyLog) {
					if (this.$errorTable.children('tr').length == 0)
						$("#"+RunalyzeLog.options.containerId).hide();
					else
						$("#"+RunalyzeLog.options.containerId).show();
				}

				return this;
			},

			init: function() {
				$("body").append('<div id="'+RunalyzeLog.options.containerId+'" class="toolbar at-top"></div>');
				var $container = $("#"+RunalyzeLog.options.containerId),
					clearLink = '<span onclick="RunalyzeLog.clear();" class="link"><i class="fa fa-fw fa-times"></i></span>',
					filterError = '<i class="fa fa-fw fa-minus-circle link margin-5" id="log-filter-ERROR" onclick="RunalyzeLog.filter(\'ERROR\');" alt="Filtern" />',
					filterWarning = '<i class="fa fa-fw fa-warning link margin-5" id="log-filter-WARNING" onclick="RunalyzeLog.filter(\'WARNING\');" alt="Filtern" />',
					filterInfo = '<i class="fa fa-fw fa-info-circle link margin-5" id="log-filter-INFO" onclick="RunalyzeLog.filter(\'INFO\');" alt="Filtern" />',
					filterLinks = filterError+filterWarning+filterInfo,
					table = '<table class="fullwidth nomargin"><thead><tr><th style="width:100px;">'+filterLinks+'</th><th>Fehlermeldungen</th><th style="width:70px;"></th><th style="width:3px;">'+clearLink+'</th></thead><tbody id="errorTable"></tbody></table>';

				$container.append('<div class="toolbar-content" style="max-height:200px;overflow-y:scroll;">'+table+'</div>');
				$container.append('<div class="toolbar-nav"><div class="toolbar-opener"></div>');

				this.$errorTable = $("#errorTable");
				this.checkVisibility();
			},

			clear: function() {
				this.$errorTable.children('tr').remove();

				return this;
			},

			remove: function(i) {
				$("#"+RunalyzeLog.idFor(i)).remove();

				return this.checkVisibility();
			},

			clear: function() {
				this.$errorTable.empty();

				return this.checkVisibility();
			},

			filter: function(type) {
				$("#log-filter-"+type).toggleClass('unimportant');
				this.$errorTable.children("."+type).toggleClass('hide');

				if (type == "INFO") {
					this.$errorTable.children(".TODO, .DEBUG, .NOTICE").toggleClass('hide');
				}
			},

			addArray: function(array) {
				for (var i in array)
					this.add(array[i]["type"], array[i]["message"]);
			},

			add: function(type, message) {
				this.iterator++;
				this.$errorTable.prepend('<tr id="'+RunalyzeLog.idFor(RunalyzeLog.iterator)+'" class="'+type+'"><td class="b errortype">'+type+'</td><td>'+message+'</td><td class="small">'+(new Date()).toTimeString().split(' ')[0]+'</td><td>'+RunalyzeLog.iconFor(RunalyzeLog.iterator)+'</td></tr>');
				Runalyze.initToggle();

				return this.checkVisibility();
			},

			idFor: function(i) {
				return 'log_'+i;
			},

			iconFor: function(i) {
				return '<span onclick="RunalyzeLog.remove('+i+');" class="link"><i class="fa fa-fw fa-times fa-grey"></i></span>';
			}
	}

	function a(a) {
		return a;
	}

	if (!window.RunalyzeLog)
		window.RunalyzeLog = RunalyzeLog;
})();