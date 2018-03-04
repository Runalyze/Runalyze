var RunalyzeLeaflet = (function(Math){

    // Public

    var self = {};


    // Private

    var maps = [];


    // Public Methods

    self.create = function(id, options) {
        if (maps.hasOwnProperty(id)) {
            self.delete(id);
        }

        maps[id] = RunalyzeMap(id, options);

        return maps[id];
    };

    self.get = function(id) {
        return maps[id];
    };

    self.delete = function(id) {
        maps[id].delete();

        return self;
    };

    self.toggleFullscreen = function (id) {
        maps[id].toggleFullscreen();
    };

    self.enterFullscreen = function (id) {
        maps[id].enterFullscreen();
    };

    self.exitFullscreen = function (id) {
        maps[id].exitFullscreen();
    };

    self.distIcon = function(dist) {
        return L.divIcon({className: 'polyline-marker polyline-marker-dist', html: Math.ceil(dist), iconSize: [12, 12], iconAnchor: [8, 8]});
    };

    self.startIcon = function() {
        return L.divIcon({className: 'polyline-marker polyline-marker-start', iconSize: [6, 6], iconAnchor: [6, 6]});
    };

    self.endIcon = function() {
        return L.divIcon({className: 'polyline-marker polyline-marker-end', iconSize: [6, 6], iconAnchor: [6, 6]});
    };

    self.pauseIcon = function() {
        return L.divIcon({className: 'polyline-marker polyline-marker-pause', iconSize: [12, 12], iconAnchor: [8, 8]});
    };

    self.posIcon = function() {
        return L.divIcon({className: 'polyline-marker polyline-marker-pos', iconSize: [6, 6], iconAnchor: [6, 6]});
    };

    return self;
})(Math);
