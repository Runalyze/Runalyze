var RunalyzeMap = (function($, RunalyzeLeaflet){
    return function(id, opt) {
        // Public

        var self = {
            Layers: RunalyzeLeaflet.getNewLayers(),
            Routes: null
        };


        // Private

        var element = $("#" + id);
        var object = null;
        var options = {
            visible: {
                layers: true,
                scale: true
            },
            layer: "OpenStreetMap"
        };
        var mapOptions = {
            scrollWheelZoom: false,
            layer: "OpenStreetMap"
        };
        var controls = {
            layers: L.control.layers(self.Layers),
            scale: L.control.scale({
                imperial: false
            })
        };


        // Private Methods

        function initControls() {
            if (options.visible.layers && controls.layers) {
                controls.layers.addTo(self.map());
            }

            if (options.visible.scale && controls.scale) {
                controls.scale.addTo(self.map());
            }

            $('<a class="leaflet-control-zoom-full" href="javascript:RunalyzeLeaflet.toggleFullscreen(\''+id+'\');" title="Fullscreen"><i class="fa fa-expand"></i></a>').insertAfter('.leaflet-control-zoom-in');

            object.on('baselayerchange', function (e) {
                if (options.layer != e.name) {
                    self.setDefaultLayer(e.name);
                    Runalyze.Config.setLeafletLayer(e.name);
                }
            });
        }

        function initTooltip() {
            object.on('mouseover', function () {
                $('<div id="polyline-info" class="tooltip right" style="display:none;"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>').appendTo($('#' + id));
            });
            object.on('mouseout', function () {
                $('#polyline-info').remove();
            });
        }


        // Public Methods

        self.map = function () {
            return object;
        };

        self.id = function () {
            return id;
        };

        self.element = function () {
            return element;
        };

        self.delete = function() {
            self.Routes.removeAllRoutes();
            self.Routes.deletePosMarker();

            try {
                object.remove();
            } catch (e) {
            }
        };

        self.setDefaultLayer = function (layer) {
            options.layer = layer;

            return self;
        };

        self.toggleFullscreen = function () {
            if (element.hasClass('fullscreen')) {
                self.exitFullscreen();
            } else {
                self.enterFullscreen();
            }
        };

        self.enterFullscreen = function () {
            element.addClass('fullscreen');
            $(".leaflet-control-zoom-full > i").removeClass('fa-expand').addClass('fa-compress');

            object.scrollWheelZoom.enable();
            object._onResize();
        };

        self.exitFullscreen = function () {
            element.removeClass('fullscreen');
            $(".leaflet-control-zoom-full > i").addClass('fa-expand').removeClass('fa-compress');

            if (!mapOptions.scrollWheelZoom) {
                object.scrollWheelZoom.disable();
            }

            object._onResize();
        };

        // Constructor

        mapOptions = $.extend({}, opt, mapOptions);
        options.layer = mapOptions.layer;
        object = L.map(id, mapOptions).addLayer(self.Layers[options.layer] || self.Layers[Object.keys(self.Layers)[0]]);

        initControls();
        initTooltip();

        self.Routes = RunalyzeLeaflet.Routes(self);

        return self;
    };
})(jQuery, RunalyzeLeaflet);
