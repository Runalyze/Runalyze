Runalyze.Common = (function($){

    var self = {};

	self.parseDate = function(input, format) {
        format = format || 'yyyy-mm-dd';
        var parts = input.match(/(\d+)/g), i = 0, fmt = {};

        if (null === parts) {
            return new Date();
        }

        format.replace(/(yyyy|dd|mm)/g, function(part) { fmt[part] = i++; });

        return new Date(parts[fmt['yyyy']], parts[fmt['mm']]-1, parts[fmt['dd']]);
    };

    self.stringToSeconds = function(string) {
        var d = 0, h  = 0, m = 0, s = 0, ms = 0,
            milisec = string.split(","),
            days    = milisec[0].split("d "),
            time = [];

        if (days.length > 1) {
            d = Number(days[0]);
            time = days[1];
        } else {
            time = days[0];
        }

        time = time.split(":");

        if (milisec.length > 1) {
            if (1 === milisec[1].length)
                ms = Number(milisec[1])/10;
            else
                ms = Number(milisec[1])/100;
        }

        if (1 === time.length)
            s = Number(time[0]);
        else if (2 === time.length) {
            m = Number(time[0]);
            s = Number(time[1]);
        } else {
            h = Number(time[0]);
            m = Number(time[1]);
            s = Number(time[2]);
        }

        return d*86400 + h*3600 + m*60 + s + ms/100;
    };

    self.secondsToString = function(s) {
        var date = new Date(null);
        date.setSeconds(s - 60*60);

        return date.toTimeString().substr(0, 8);
    };

	return self;
})(jQuery);
