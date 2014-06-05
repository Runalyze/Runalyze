/*
 * Lib for logging errors in Runalyze
 * 
 * (c) 2012 Hannes Christiansen, http://www.runalyze.de/
 */
var RunalyzeLog = (function($, parent){

	// Public

	var self = {};


	// Private

	var _id = 'error-toolbar',
		_$table = null,
		_$container = null,
		_iterator = 0;


	// Private Methods

	function _idFor(i) {
		return 'log-'+i;
	}

	function _iconFor(i) {
		return '<span onclick="RunalyzeLog.remove('+i+');" class="link"><i class="fa fa-fw fa-times fa-grey"></i></span>';
	}

	function _checkVisibility() {
		if (_$table.children('tr').length == 0)
			_$container.hide();
		else
			_$container.show();
	}


	// Public Methods

	self.init = function() {
		$("body").append('<div id="' + _id + '" class="toolbar at-top"></div>');

		_$container = $('#' + _id);

		var clear = '<span onclick="RunalyzeLog.clear();" class="link"><i class="fa fa-fw fa-times"></i></span>',
			error = '<i class="fa fa-fw fa-minus-circle link margin-5" id="log-filter-ERROR" onclick="RunalyzeLog.filter(\'ERROR\');" />',
			warning = '<i class="fa fa-fw fa-warning link margin-5" id="log-filter-WARNING" onclick="RunalyzeLog.filter(\'WARNING\');" />',
			info = '<i class="fa fa-fw fa-info-circle link margin-5" id="log-filter-INFO" onclick="RunalyzeLog.filter(\'INFO\');" />',
			filter = error + warning + info,
			table = '<table class="fullwidth nomargin"><thead><tr><th style="width:100px;">'+filter+'</th><th>Errors</th><th style="width:70px;"></th><th style="width:3px;">'+clear+'</th></thead><tbody id="errorTable"></tbody></table>';

		_$container.append('<div class="toolbar-content" style="max-height:200px;overflow-y:scroll;">'+table+'</div>');
		_$container.append('<div class="toolbar-nav"><div class="toolbar-opener"></div>');

		_$table = $("#errorTable");
		_checkVisibility();
	};

	self.remove = function(i) {
		$('#' + _idFor(i)).remove();

		_checkVisibility();

		return self;
	};

	self.clear = function(i) {
		_$table.empty();

		_checkVisibility();

		return self;
	};

	self.filter = function(type) {
		$('#log-filter-'+type).toggleClass('unimportant');
		_$table.children('.'+type).toggleClass('hide');

		if (type == 'INFO')
			_$table.children('.TODO, .DEBUG, .NOTICE').toggleClass('hide');
	};

	self.addArray = function(array) {
		for (var i in array)
			self.add(array[i]['type'], array[i]['message']);
	};

	self.add = function(type, message) {
		_iterator++;
		_$table.prepend('<tr id="' + _idFor(_iterator) + '" class="' + type + '">' +
			'<td class="b errortype">' + type + '</td><td>' + message + '</td>' +
			'<td class="small">' + (new Date()).toTimeString().split(' ')[0] + '</td><td>' + _iconFor(_iterator) +
			'</td></tr>');

		Runalyze.initToggle();

		_checkVisibility();

		return self;
	}

	return self;
})(jQuery, undefined);