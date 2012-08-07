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
				containerId: "errorToolbar",
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
				$("body").append('<div id="'+RunalyzeLog.options.containerId+'" class="toolbar atTop"></div>');
				var $container = $("#"+RunalyzeLog.options.containerId),
					clearLink = '<img onclick="RunalyzeLog.clear();" alt="Log leeren" src="img/delete_gray.gif" class="link" />',
					filterError = '<img id="log-filter-ERROR" onclick="RunalyzeLog.filter(\'ERROR\');" alt="Filtern" src="img/error.gif" class="link margin-5" />',
					filterWarning = '<img id="log-filter-WARNING" onclick="RunalyzeLog.filter(\'WARNING\');" alt="Filtern" src="img/warning.png" class="link margin-5" />',
					filterInfo = '<img id="log-filter-INFO" onclick="RunalyzeLog.filter(\'INFO\');" alt="Filtern" src="img/info.gif" class="link margin-5" />',
					filterLinks = filterError+filterWarning+filterInfo,
					table = '<table class="fullWidth nomargin"><thead><tr><th style="width:100px;">'+filterLinks+'</th><th>Fehlermeldungen</th><th style="width:70px;"></th><th style="width:3px;">'+clearLink+'</th></thead><tbody id="errorTable"></tbody></table>';

				$container.append('<div class="toolbar-content" style="max-height:200px;overflow-y:scroll;">'+table+'</div>');
				$container.append('<div class="toolbar-nav"><div class="toolbar-opener"></div>');

				this.$errorTable = $("#errorTable");
				this.checkVisibility();

				this.add('TODO', 'Make me work!');
				this.add('WARNING', 'Unsafe version. Please update.');
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
				return '<img onclick="RunalyzeLog.remove('+i+');" alt="Eintrag entfernen" src="img/delete_gray.gif" class="link" />';
			}
	}

	function a(a) {
		return a;
	}

	if (!window.RunalyzeLog)
		window.RunalyzeLog = RunalyzeLog;
})();