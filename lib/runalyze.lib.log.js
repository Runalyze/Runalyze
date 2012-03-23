/*
 * Lib for logging errors in Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
(function(){
	var RunalyzeLog = {
			errorTypes: ["todo", "info", "debug", "notice", "error", "warning"],
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
					line = '', // <div class="toolbar-line"><a href="#">Fehler ausblenden</a></div>
					table = '<table class="fullWidth nomargin"><thead><tr><th style="width:100px;"></th><th>Fehlermeldungen</th><th style="width:70px;"></th><th style="width:3px;"></th></thead><tbody id="errorTable"></tbody></table>';

				$container.append('<div class="toolbar-content" style="max-height:200px;overflow-y:scroll;">'+line+table+'</div>');
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
				return '<img onclick="RunalyzeLog.remove('+i+');" alt="Eintrag entfernen" src="img/delete_gray.gif" class="link">';
			}
	}

	function a(a) {
		return a;
	}

	if (!window.RunalyzeLog)
		window.RunalyzeLog = RunalyzeLog;
})();