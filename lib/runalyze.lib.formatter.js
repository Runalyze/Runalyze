Runalyze.Formatter = (function($, common){

    var paceUnits = [
        { appendix: 'min/km', factor: 1.0, isDecimal: false },
        { appendix: 'min/mi', factor: 1.0 / 0.621371192, isDecimal: false },
        { appendix: 'min/500m', factor: 0.5, isDecimal: false },
        { appendix: 'min/500y', factor: 0.5 / 1.0936133, isDecimal: false },
        { appendix: 'min/100m', factor: 0.1, isDecimal: false },
        { appendix: 'min/100y', factor: 0.1 / 1.0936133, isDecimal: false },
        { appendix: 'km/h', factor: 1.0 / 3600, isDecimal: true },
        { appendix: 'mph', factor: 1.0 / 3600 / 0.621371192, isDecimal: true },
        { appendix: 'm/s', factor: 1.0 / 1000, isDecimal: true }
    ];

    var self = {};

    self.formatValue = function(value, decimals, unit) {
        if (decimals !== false) {
            value = value.toFixed(decimals);
        }

        if (unit !== false && '' !== unit) {
            return value + '' + unit;
        }

        return value + '';
    };

    self.formatKilometer = function(distance, decimals, withUnit) {
        decimals = decimals || 2;
        withUnit = withUnit || true;

        return self.formatValue(distance, decimals, withUnit ? ' km' : '');
    };

    self.formatPace = function(secondsPerKilometer, internalUnitEnum, withUnit) {
        var pace = self.getPaceObject(secondsPerKilometer, internalUnitEnum);

        if (withUnit === "short") {
            return pace.string + pace.shortAppendix;
        } else if (withUnit) {
            return pace.string + pace.appendix;
        }

        return pace.string;
    };

    self.getPaceObject = function(secondsPerKilometer, internalUnitEnum) {
        if (typeof paceUnits[internalUnitEnum] !== "object") {
            internalUnitEnum = 0;
        }

        var paceUnit = paceUnits[internalUnitEnum];
        var value = secondsPerKilometer * paceUnit.factor;

        if (paceUnit.isDecimal && value > 0) {
            value = 1 / value;
        }

        return {
            value: value,
            string: value == 0 ? (paceUnit.isDecimal ? '-' : '-:--') : (
                paceUnit.isDecimal ? value.toFixed(1) : common.secondsToString(value, true)
            ),
            appendix: paceUnit.appendix,
            shortAppendix: paceUnit.appendix.substr(0, 4) === "min/" ? paceUnit.appendix.substr(3) : paceUnit.appendix,
            isDecimal: paceUnit.isDecimal
        };
    };

    return self;
})(jQuery, Runalyze.Common);
