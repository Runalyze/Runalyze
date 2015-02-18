/*
 * Lib for logging errors in Runalyze
 * 
 * (c) 2014 Hannes Christiansen, http://www.runalyze.de/
 */
Runalyze.Log = (function($, Parent){

	// Public

	var self = {};


	// Private

	var id = 'error-toolbar';
	var iterator = 0;

	var $table = null;
	var $container = null;


	// Private Methods

	function idFor(i) {
		return 'log-'+i;
	}

	function iconFor(i) {
		return '<span onclick="Runalyze.Log.remove('+i+');" class="link"><i class="fa fa-fw fa-times fa-grey"></i></span>';
	}

	function checkVisibility() {
		if ($table.children('tr').length == 0)
			$container.hide();
		else
			$container.show();
	}


	// Public Methods

	self.init = function() {
		if ($container)
			return;

		$("body").append('<div id="' + id + '" class="toolbar at-top"></div>');

		$container = $('#' + id);

		var clear = '<span onclick="Runalyze.Log.clear();" class="link"><i class="fa fa-fw fa-times"></i></span>',
			error = '<i class="fa fa-fw fa-minus-circle link margin-5" id="log-filter-ERROR" onclick="Runalyze.Log.filter(\'ERROR\');" />',
			warning = '<i class="fa fa-fw fa-warning link margin-5" id="log-filter-WARNING" onclick="Runalyze.Log.filter(\'WARNING\');" />',
			info = '<i class="fa fa-fw fa-info-circle link margin-5" id="log-filter-INFO" onclick="Runalyze.Log.filter(\'INFO\');" />',
			filter = error + warning + info,
			table = '<table class="fullwidth nomargin"><thead><tr><th style="width:100px;">'+filter+'</th><th>Errors</th><th style="width:70px;"></th><th style="width:3px;">'+clear+'</th></thead><tbody id="errorTable"></tbody></table>';

		$container.append('<div class="toolbar-content" style="max-height:200px;overflow-y:scroll;">'+table+'</div>');
		$container.append('<div class="toolbar-nav"><div class="toolbar-opener"></div>');

		$table = $("#errorTable");
		checkVisibility();
	};

	self.remove = function(i) {
		$('#' + idFor(i)).remove();

		checkVisibility();

		return self;
	};

	self.clear = function(i) {
		$table.empty();

		checkVisibility();

		return self;
	};

	self.filter = function(type) {
		$('#log-filter-'+type).toggleClass('unimportant');
		$table.children('.'+type).toggleClass('hide');

		if (type == 'INFO')
			$table.children('.TODO, .DEBUG, .NOTICE').toggleClass('hide');
	};

	self.addArray = function(array) {
		for (var i in array)
			self.add(array[i]['type'], array[i]['message']);
	};

	self.add = function(type, message) {
		iterator++;
		$table.prepend('<tr id="' + idFor(iterator) + '" class="' + type + '">' +
			'<td class="b errortype">' + type + '</td><td>' + message + '</td>' +
			'<td class="small">' + (new Date()).toTimeString().split(' ')[0] + '</td><td>' + iconFor(iterator) +
			'</td></tr>');

		Parent.Feature.initToggle();

		checkVisibility();

		return self;
	};

	Parent.addInitHook('init-log', self.init);

	return self;
})(jQuery, Runalyze);