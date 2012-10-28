/**
 * Flot plugin for drawing text (ticks, values, legends, etc...) directly on FLOT's canvas context
 * Released by Andre Lessa, September 2010, v.0.1
 * http://www.lessaworld.com/projects/flotCanvasText
 *
 * Check the site above for updates. 

  As of FLOT's release 0.6, tick labels and the legends are all created using
  HTML DIVs (i.e. not created on the Canvas). As one of the consequences, you can't use
  the canvasInstance.toDataURL("image/png") method to export the graph to an image.
  source: http://code.google.com/p/flot/
   
  This plugin is compatible with Internet Explorer when you use the ExCanvas library.
  It does render the text in Internet Explorer.
  However, since IE doesn't implement the .toDataURL() method, you still cannot export the graph.
 
  This plugin has been tested in Safari 4, Firefox 3+, and IE 7+

  Some Examples:
 
       plot = $.plot(...);

       // enables the canvasText plugin
       
            plot.grid({ canvasText: {show: true});
   
       // enables the canvasText plugin, sets the font size to 9px, and requests that the Y-AXIS values for series 0, 2 and 5 be plotted on the graph..
       
            plot.grid({ canvasText: {show: true, font: "sans 9px", series: [0,2,5] }});
 
       // optionally, you can also provide a function to format the plotting of a specific series Y values
       // and if you do that, you can also provide a Scope object that becomes accessible within that function call
       
           scopeObj = {someValue: 323};
           seriesValueFormat = function(contextObj, paramObj){
                var r = {};
                if (paramObj.someValue > 100){
                   r.label =  "High";
                } else {
                   r.label =  contextObj.y;
                }
                r.color = "#7C7C7C";  // You can also use the special values #series and #grid to use those specific colors.
               }
               return r;
           };      
           plot.grid({ canvasText: {show: true, font: "sans 9px", series: [[0, seriesValueFormat, scopeObj]], seriesFont: "sans 8px"}});
       
       // optionally, you can also request X-AXIS tick labels to have line breaks after each word
       
           plot.grid({ canvasText: {show: true, font: "sans 9px", lineBreaks: {show: true} }});

       // further format control is also made available so you can play with the placements of the labels

          plot.grid({ canvasText: {show: true, font: "sans 9px", lineBreaks: {show: true, marginTop: 3, marginBottom: 5, lineSpacing: 1} }});


  Default options:

     grid: {
         canvasText: {
             show: false,
             font: "sans 8px",
             series: null,
             seriesFont: "sans 8px",
             lineBreaks: {show: false, marginTop: 3, marginBottom: 5, lineSpacing: 1}
         }
     }
   
  By having canvasText default to false, it defaults to FLOT's out-of-the-box HTML-driven approach. 
  Setting it to true, enables this plugin and draws all the text (ticks and legends) on the canvas.
  Although the *only* font available is "sans", you can use the canvasText.font setting to set the font size.
  If canvasText.font is not provided, it defaults to 8px.
 
  IMPORTANT NOTE:
  The original code for the CanvasTextFunctions object was released to the public domain by Jim Studt, 2007.
  source: http://jim.studt.net/canvastext/
  He may also keep some sort of up to date copy at http://www.federated.com/~jim/canvastext/

  Suggestions and Bug Findings are welcome!

  Cheers!
  Andre Lessa
  andre@lessaworld.com

 *
 */

(function ($) {
    var options = {
        grid: {
            canvasText: {
               show: false,
               font: "sans 8px",
               series: null,
               seriesFont: "sans 8px",
               lineBreaks: {show: false, marginTop: 3, marginBottom: 5, lineSpacing: 1}
            }
        }
    };

    function init(plot) {
    
        /**
        * Starting here, I used the code in the Public Domain.
        * A few modifications were made, but almost all of it came from the canvastext project.
        */
        var CanvasTextFunctions = { };
        
        CanvasTextFunctions.font = "sans";
        CanvasTextFunctions.fontSize = 8;
            
        // [0,0] indicates the left bottom position
        // Fixed the ; character and added the '≥' character to the set as it was necessary for my project.
        CanvasTextFunctions.letters = {
            ' ': { width: 16, points: [] },
            '!': { width: 10, points: [[5,21],[5,7],[-1,-1],[5,2],[4,1],[5,0],[6,1],[5,2]] },
            '"': { width: 16, points: [[4,21],[4,14],[-1,-1],[12,21],[12,14]] },
            '#': { width: 21, points: [[11,25],[4,-7],[-1,-1],[17,25],[10,-7],[-1,-1],[4,12],[18,12],[-1,-1],[3,6],[17,6]] },
            '$': { width: 20, points: [[8,25],[8,-4],[-1,-1],[12,25],[12,-4],[-1,-1],[17,18],[15,20],[12,21],[8,21],[5,20],[3,18],[3,16],[4,14],[5,13],[7,12],[13,10],[15,9],[16,8],[17,6],[17,3],[15,1],[12,0],[8,0],[5,1],[3,3]] },
            '%': { width: 24, points: [[21,21],[3,0],[-1,-1],[8,21],[10,19],[10,17],[9,15],[7,14],[5,14],[3,16],[3,18],[4,20],[6,21],[8,21],[10,20],[13,19],[16,19],[19,20],[21,21],[-1,-1],[17,7],[15,6],[14,4],[14,2],[16,0],[18,0],[20,1],[21,3],[21,5],[19,7],[17,7]] },
            '&': { width: 26, points: [[23,12],[23,13],[22,14],[21,14],[20,13],[19,11],[17,6],[15,3],[13,1],[11,0],[7,0],[5,1],[4,2],[3,4],[3,6],[4,8],[5,9],[12,13],[13,14],[14,16],[14,18],[13,20],[11,21],[9,20],[8,18],[8,16],[9,13],[11,10],[16,3],[18,1],[20,0],[22,0],[23,1],[23,2]] },
            '\'': { width: 10, points: [[5,19],[4,20],[5,21],[6,20],[6,18],[5,16],[4,15]] },
            '(': { width: 14, points: [[11,25],[9,23],[7,20],[5,16],[4,11],[4,7],[5,2],[7,-2],[9,-5],[11,-7]] },
            ')': { width: 14, points: [[3,25],[5,23],[7,20],[9,16],[10,11],[10,7],[9,2],[7,-2],[5,-5],[3,-7]] },
            '*': { width: 16, points: [[8,21],[8,9],[-1,-1],[3,18],[13,12],[-1,-1],[13,18],[3,12]] },
            '+': { width: 26, points: [[13,18],[13,0],[-1,-1],[4,9],[22,9]] },
            ',': { width: 10, points: [[6,1],[5,0],[4,1],[5,2],[6,1],[6,-1],[5,-3],[4,-4]] },
            '-': { width: 26, points: [[4,9],[22,9]] },
            '.': { width: 10, points: [[5,2],[4,1],[5,0],[6,1],[5,2]] },
            '/': { width: 22, points: [[20,25],[2,-7]] },
            '0': { width: 20, points: [[9,21],[6,20],[4,17],[3,12],[3,9],[4,4],[6,1],[9,0],[11,0],[14,1],[16,4],[17,9],[17,12],[16,17],[14,20],[11,21],[9,21]] },
            '1': { width: 20, points: [[6,17],[8,18],[11,21],[11,0]] },
            '2': { width: 20, points: [[4,16],[4,17],[5,19],[6,20],[8,21],[12,21],[14,20],[15,19],[16,17],[16,15],[15,13],[13,10],[3,0],[17,0]] },
            '3': { width: 20, points: [[5,21],[16,21],[10,13],[13,13],[15,12],[16,11],[17,8],[17,6],[16,3],[14,1],[11,0],[8,0],[5,1],[4,2],[3,4]] },
            '4': { width: 20, points: [[13,21],[3,7],[18,7],[-1,-1],[13,21],[13,0]] },
            '5': { width: 20, points: [[15,21],[5,21],[4,12],[5,13],[8,14],[11,14],[14,13],[16,11],[17,8],[17,6],[16,3],[14,1],[11,0],[8,0],[5,1],[4,2],[3,4]] },
            '6': { width: 20, points: [[16,18],[15,20],[12,21],[10,21],[7,20],[5,17],[4,12],[4,7],[5,3],[7,1],[10,0],[11,0],[14,1],[16,3],[17,6],[17,7],[16,10],[14,12],[11,13],[10,13],[7,12],[5,10],[4,7]] },
            '7': { width: 20, points: [[17,21],[7,0],[-1,-1],[3,21],[17,21]] },
            '8': { width: 20, points: [[8,21],[5,20],[4,18],[4,16],[5,14],[7,13],[11,12],[14,11],[16,9],[17,7],[17,4],[16,2],[15,1],[12,0],[8,0],[5,1],[4,2],[3,4],[3,7],[4,9],[6,11],[9,12],[13,13],[15,14],[16,16],[16,18],[15,20],[12,21],[8,21]] },
            '9': { width: 20, points: [[16,14],[15,11],[13,9],[10,8],[9,8],[6,9],[4,11],[3,14],[3,15],[4,18],[6,20],[9,21],[10,21],[13,20],[15,18],[16,14],[16,9],[15,4],[13,1],[10,0],[8,0],[5,1],[4,3]] },
            ':': { width: 10, points: [[5,14],[4,13],[5,12],[6,13],[5,14],[-1,-1],[5,2],[4,1],[5,0],[6,1],[5,2]] },
            ';': { width: 10, points: [[5,14],[4,13],[5,12],[6,13],[5,14],[-1,-1],[6,1],[5,0],[4,1],[5,2],[6,1],[6,-1],[5,-3],[4,-4]] },
            '<': { width: 24, points: [[20,18],[4,9],[20,0]] },
            '=': { width: 26, points: [[4,12],[22,12],[-1,-1],[4,6],[22,6]] },
            '>': { width: 24, points: [[4,18],[20,9],[4,0]] },
            '≥': { width: 24, points: [[4,18],[20,12],[4,7],[-1,-1],[4,2],[20,2]] },
            '?': { width: 18, points: [[3,16],[3,17],[4,19],[5,20],[7,21],[11,21],[13,20],[14,19],[15,17],[15,15],[14,13],[13,12],[9,10],[9,7],[-1,-1],[9,2],[8,1],[9,0],[10,1],[9,2]] },
            '@': { width: 27, points: [[18,13],[17,15],[15,16],[12,16],[10,15],[9,14],[8,11],[8,8],[9,6],[11,5],[14,5],[16,6],[17,8],[-1,-1],[12,16],[10,14],[9,11],[9,8],[10,6],[11,5],[-1,-1],[18,16],[17,8],[17,6],[19,5],[21,5],[23,7],[24,10],[24,12],[23,15],[22,17],[20,19],[18,20],[15,21],[12,21],[9,20],[7,19],[5,17],[4,15],[3,12],[3,9],[4,6],[5,4],[7,2],[9,1],[12,0],[15,0],[18,1],[20,2],[21,3],[-1,-1],[19,16],[18,8],[18,6],[19,5]] },
            'A': { width: 18, points: [[9,21],[1,0],[-1,-1],[9,21],[17,0],[-1,-1],[4,7],[14,7]] },
            'B': { width: 21, points: [[4,21],[4,0],[-1,-1],[4,21],[13,21],[16,20],[17,19],[18,17],[18,15],[17,13],[16,12],[13,11],[-1,-1],[4,11],[13,11],[16,10],[17,9],[18,7],[18,4],[17,2],[16,1],[13,0],[4,0]] },
            'C': { width: 21, points: [[18,16],[17,18],[15,20],[13,21],[9,21],[7,20],[5,18],[4,16],[3,13],[3,8],[4,5],[5,3],[7,1],[9,0],[13,0],[15,1],[17,3],[18,5]] },
            'D': { width: 21, points: [[4,21],[4,0],[-1,-1],[4,21],[11,21],[14,20],[16,18],[17,16],[18,13],[18,8],[17,5],[16,3],[14,1],[11,0],[4,0]] },
            'E': { width: 19, points: [[4,21],[4,0],[-1,-1],[4,21],[17,21],[-1,-1],[4,11],[12,11],[-1,-1],[4,0],[17,0]] },
            'F': { width: 18, points: [[4,21],[4,0],[-1,-1],[4,21],[17,21],[-1,-1],[4,11],[12,11]] },
            'G': { width: 21, points: [[18,16],[17,18],[15,20],[13,21],[9,21],[7,20],[5,18],[4,16],[3,13],[3,8],[4,5],[5,3],[7,1],[9,0],[13,0],[15,1],[17,3],[18,5],[18,8],[-1,-1],[13,8],[18,8]] },
            'H': { width: 22, points: [[4,21],[4,0],[-1,-1],[18,21],[18,0],[-1,-1],[4,11],[18,11]] },
            'I': { width: 8, points: [[4,21],[4,0]] },
            'J': { width: 16, points: [[12,21],[12,5],[11,2],[10,1],[8,0],[6,0],[4,1],[3,2],[2,5],[2,7]] },
            'K': { width: 21, points: [[4,21],[4,0],[-1,-1],[18,21],[4,7],[-1,-1],[9,12],[18,0]] },
            'L': { width: 17, points: [[4,21],[4,0],[-1,-1],[4,0],[16,0]] },
            'M': { width: 24, points: [[4,21],[4,0],[-1,-1],[4,21],[12,0],[-1,-1],[20,21],[12,0],[-1,-1],[20,21],[20,0]] },
            'N': { width: 22, points: [[4,21],[4,0],[-1,-1],[4,21],[18,0],[-1,-1],[18,21],[18,0]] },
            'O': { width: 22, points: [[9,21],[7,20],[5,18],[4,16],[3,13],[3,8],[4,5],[5,3],[7,1],[9,0],[13,0],[15,1],[17,3],[18,5],[19,8],[19,13],[18,16],[17,18],[15,20],[13,21],[9,21]] },
            'P': { width: 21, points: [[4,21],[4,0],[-1,-1],[4,21],[13,21],[16,20],[17,19],[18,17],[18,14],[17,12],[16,11],[13,10],[4,10]] },
            'Q': { width: 22, points: [[9,21],[7,20],[5,18],[4,16],[3,13],[3,8],[4,5],[5,3],[7,1],[9,0],[13,0],[15,1],[17,3],[18,5],[19,8],[19,13],[18,16],[17,18],[15,20],[13,21],[9,21],[-1,-1],[12,4],[18,-2]] },
            'R': { width: 21, points: [[4,21],[4,0],[-1,-1],[4,21],[13,21],[16,20],[17,19],[18,17],[18,15],[17,13],[16,12],[13,11],[4,11],[-1,-1],[11,11],[18,0]] },
            'S': { width: 20, points: [[17,18],[15,20],[12,21],[8,21],[5,20],[3,18],[3,16],[4,14],[5,13],[7,12],[13,10],[15,9],[16,8],[17,6],[17,3],[15,1],[12,0],[8,0],[5,1],[3,3]] },
            'T': { width: 16, points: [[8,21],[8,0],[-1,-1],[1,21],[15,21]] },
            'U': { width: 22, points: [[4,21],[4,6],[5,3],[7,1],[10,0],[12,0],[15,1],[17,3],[18,6],[18,21]] },
            'V': { width: 18, points: [[1,21],[9,0],[-1,-1],[17,21],[9,0]] },
            'W': { width: 24, points: [[2,21],[7,0],[-1,-1],[12,21],[7,0],[-1,-1],[12,21],[17,0],[-1,-1],[22,21],[17,0]] },
            'X': { width: 20, points: [[3,21],[17,0],[-1,-1],[17,21],[3,0]] },
            'Y': { width: 18, points: [[1,21],[9,11],[9,0],[-1,-1],[17,21],[9,11]] },
            'Z': { width: 20, points: [[17,21],[3,0],[-1,-1],[3,21],[17,21],[-1,-1],[3,0],[17,0]] },
            '[': { width: 14, points: [[4,25],[4,-7],[-1,-1],[5,25],[5,-7],[-1,-1],[4,25],[11,25],[-1,-1],[4,-7],[11,-7]] },
            '\\': { width: 14, points: [[0,21],[14,-3]] },
            ']': { width: 14, points: [[9,25],[9,-7],[-1,-1],[10,25],[10,-7],[-1,-1],[3,25],[10,25],[-1,-1],[3,-7],[10,-7]] },
            '^': { width: 16, points: [[6,15],[8,18],[10,15],[-1,-1],[3,12],[8,17],[13,12],[-1,-1],[8,17],[8,0]] },
            '_': { width: 16, points: [[0,-2],[16,-2]] },
            '`': { width: 10, points: [[6,21],[5,20],[4,18],[4,16],[5,15],[6,16],[5,17]] },
            'a': { width: 19, points: [[15,14],[15,0],[-1,-1],[15,11],[13,13],[11,14],[8,14],[6,13],[4,11],[3,8],[3,6],[4,3],[6,1],[8,0],[11,0],[13,1],[15,3]] },
            'b': { width: 19, points: [[4,21],[4,0],[-1,-1],[4,11],[6,13],[8,14],[11,14],[13,13],[15,11],[16,8],[16,6],[15,3],[13,1],[11,0],[8,0],[6,1],[4,3]] },
            'c': { width: 18, points: [[15,11],[13,13],[11,14],[8,14],[6,13],[4,11],[3,8],[3,6],[4,3],[6,1],[8,0],[11,0],[13,1],[15,3]] },
            'd': { width: 19, points: [[15,21],[15,0],[-1,-1],[15,11],[13,13],[11,14],[8,14],[6,13],[4,11],[3,8],[3,6],[4,3],[6,1],[8,0],[11,0],[13,1],[15,3]] },
            'e': { width: 18, points: [[3,8],[15,8],[15,10],[14,12],[13,13],[11,14],[8,14],[6,13],[4,11],[3,8],[3,6],[4,3],[6,1],[8,0],[11,0],[13,1],[15,3]] },
            'f': { width: 12, points: [[10,21],[8,21],[6,20],[5,17],[5,0],[-1,-1],[2,14],[9,14]] },
            'g': { width: 19, points: [[15,14],[15,-2],[14,-5],[13,-6],[11,-7],[8,-7],[6,-6],[-1,-1],[15,11],[13,13],[11,14],[8,14],[6,13],[4,11],[3,8],[3,6],[4,3],[6,1],[8,0],[11,0],[13,1],[15,3]] },
            'h': { width: 19, points: [[4,21],[4,0],[-1,-1],[4,10],[7,13],[9,14],[12,14],[14,13],[15,10],[15,0]] },
            'i': { width: 8, points: [[3,21],[4,20],[5,21],[4,22],[3,21],[-1,-1],[4,14],[4,0]] },
            'j': { width: 10, points: [[5,21],[6,20],[7,21],[6,22],[5,21],[-1,-1],[6,14],[6,-3],[5,-6],[3,-7],[1,-7]] },
            'k': { width: 17, points: [[4,21],[4,0],[-1,-1],[14,14],[4,4],[-1,-1],[8,8],[15,0]] },
            'l': { width: 8, points: [[4,21],[4,0]] },
            'm': { width: 30, points: [[4,14],[4,0],[-1,-1],[4,10],[7,13],[9,14],[12,14],[14,13],[15,10],[15,0],[-1,-1],[15,10],[18,13],[20,14],[23,14],[25,13],[26,10],[26,0]] },
            'n': { width: 19, points: [[4,14],[4,0],[-1,-1],[4,10],[7,13],[9,14],[12,14],[14,13],[15,10],[15,0]] },
            'o': { width: 19, points: [[8,14],[6,13],[4,11],[3,8],[3,6],[4,3],[6,1],[8,0],[11,0],[13,1],[15,3],[16,6],[16,8],[15,11],[13,13],[11,14],[8,14]] },
            'p': { width: 19, points: [[4,14],[4,-7],[-1,-1],[4,11],[6,13],[8,14],[11,14],[13,13],[15,11],[16,8],[16,6],[15,3],[13,1],[11,0],[8,0],[6,1],[4,3]] },
            'q': { width: 19, points: [[15,14],[15,-7],[-1,-1],[15,11],[13,13],[11,14],[8,14],[6,13],[4,11],[3,8],[3,6],[4,3],[6,1],[8,0],[11,0],[13,1],[15,3]] },
            'r': { width: 13, points: [[4,14],[4,0],[-1,-1],[4,8],[5,11],[7,13],[9,14],[12,14]] },
            's': { width: 17, points: [[14,11],[13,13],[10,14],[7,14],[4,13],[3,11],[4,9],[6,8],[11,7],[13,6],[14,4],[14,3],[13,1],[10,0],[7,0],[4,1],[3,3]] },
            't': { width: 12, points: [[5,21],[5,4],[6,1],[8,0],[10,0],[-1,-1],[2,14],[9,14]] },
            'u': { width: 19, points: [[4,14],[4,4],[5,1],[7,0],[10,0],[12,1],[15,4],[-1,-1],[15,14],[15,0]] },
            'v': { width: 16, points: [[2,14],[8,0],[-1,-1],[14,14],[8,0]] },
            'w': { width: 22, points: [[3,14],[7,0],[-1,-1],[11,14],[7,0],[-1,-1],[11,14],[15,0],[-1,-1],[19,14],[15,0]] },
            'x': { width: 17, points: [[3,14],[14,0],[-1,-1],[14,14],[3,0]] },
            'y': { width: 16, points: [[2,14],[8,0],[-1,-1],[14,14],[8,0],[6,-4],[4,-6],[2,-7],[1,-7]] },
            'z': { width: 17, points: [[14,14],[3,0],[-1,-1],[3,14],[14,14],[-1,-1],[3,0],[14,0]] },
            '{': { width: 14, points: [[9,25],[7,24],[6,23],[5,21],[5,19],[6,17],[7,16],[8,14],[8,12],[6,10],[-1,-1],[7,24],[6,22],[6,20],[7,18],[8,17],[9,15],[9,13],[8,11],[4,9],[8,7],[9,5],[9,3],[8,1],[7,0],[6,-2],[6,-4],[7,-6],[-1,-1],[6,8],[8,6],[8,4],[7,2],[6,1],[5,-1],[5,-3],[6,-5],[7,-6],[9,-7]] },
            '|': { width: 8, points: [[4,25],[4,-7]] },
            '}': { width: 14, points: [[5,25],[7,24],[8,23],[9,21],[9,19],[8,17],[7,16],[6,14],[6,12],[8,10],[-1,-1],[7,24],[8,22],[8,20],[7,18],[6,17],[5,15],[5,13],[6,11],[10,9],[6,7],[5,5],[5,3],[6,1],[7,0],[8,-2],[8,-4],[7,-6],[-1,-1],[8,8],[6,6],[6,4],[7,2],[8,1],[9,-1],[9,-3],[8,-5],[7,-6],[5,-7]] },
            '~': { width: 24, points: [[3,6],[3,8],[4,11],[6,12],[8,12],[10,11],[14,8],[16,7],[18,7],[20,8],[21,10],[-1,-1],[3,8],[4,10],[6,11],[8,11],[10,10],[14,7],[16,6],[18,6],[20,7],[21,10],[21,12]] }
        };

        CanvasTextFunctions.letter = function (ch)
        {
            return CanvasTextFunctions.letters[ch];
        }

        CanvasTextFunctions.ascent = function()
        {
            var font = CanvasTextFunctions.fontName;
            var size = CanvasTextFunctions.fontSize;

            return size;
        }

        CanvasTextFunctions.descent = function()
        {
            var font = CanvasTextFunctions.fontName;
            var size = CanvasTextFunctions.fontSize;
            
            return 7.0*size/25.0;
        }

        CanvasTextFunctions.measure = function(str)
        {
            var font = CanvasTextFunctions.fontName;
            var size = CanvasTextFunctions.fontSize;

            var total = 0;
            var len = str.length;

            for ( i = 0; i < len; i++) {
            var c = CanvasTextFunctions.letter( str.charAt(i));
            if ( c) total += c.width * size / 25.0;
            }
            return total;
        }

        CanvasTextFunctions.draw = function(ctx,x,y,str)
        {
            var font = CanvasTextFunctions.fontName;
            var size = CanvasTextFunctions.fontSize;

            var total = 0;
            var len = str.length;
            var mag = size / 25.0;

            ctx.save();
            ctx.lineCap = "round";
            ctx.lineWidth = 2.0 * mag;

            for (var i = 0; i < len; i++) {
            var c = CanvasTextFunctions.letter( str.charAt(i));
            if ( !c) continue;

            ctx.beginPath();

            var penUp = 1;
            var needStroke = 0;
            for (var j = 0; j < c.points.length; j++) {
                var a = c.points[j];
                if ( a[0] == -1 && a[1] == -1) {
                penUp = 1;
                continue;
                }
                if ( penUp) {
                ctx.moveTo( x + a[0]*mag, y - a[1]*mag);
                penUp = false;
                } else {
                ctx.lineTo( x + a[0]*mag, y - a[1]*mag);
                }
            }
            ctx.stroke();
            x += c.width*mag;
            }
            ctx.restore();
            return total;
        }

        /**
        * Added this new method to the canvastext object in order to support a very, very naive
        * interface to change the font attributes. At this point, only the font size can be
        * modified.
        */
        CanvasTextFunctions.fontFamily = function(fontfamily)
        {
            var size = fontfamily.match(/ [0-9]?[0-9]px/gi);
            var font = fontfamily.match(/^[a-z]* /gi);
            CanvasTextFunctions.fontName = font;
            CanvasTextFunctions.fontSize = parseInt(size[0].replace('px','').replace(' ',''));
            return true;
        }
        
        CanvasTextFunctions.enable = function(ctx)
        {
            /**
            * Changed the signature of some methods to start trying to match the official
            * HTML5 specification. Unfortunately, AFAIK, there's no way to get the height of the
            * text in a canvas object using the HTML5 spec at its current state.
            */          
            ctx.fillText = function(text,x,y) { return CanvasTextFunctions.draw(ctx,x,y,text); };
            ctx.measureText = function(text) { return CanvasTextFunctions.measure(text); };
            ctx.fontFamily = function(fontfamily) { return CanvasTextFunctions.fontFamily(fontfamily); };
            ctx.fontAscent = function() { return CanvasTextFunctions.ascent(); }
    
            /**
            * The following helper methods are currently not required by this plugin
            */          
            ctx.fontDescent = function() { return CanvasTextFunctions.descent(); }
            ctx.drawTextRight = function(text,x,y) { 
                var w = CanvasTextFunctions.measure(text);
                return CanvasTextFunctions.draw(ctx, x-w,y,text); 
            };
            ctx.drawTextCenter = function(text,x,y) { 
                var w = CanvasTextFunctions.measure(text);
                return CanvasTextFunctions.draw(ctx, x-w/2,y,text); 
            };
        }
        /**
        * The code found in the Public Domain (along with my modifications) ends here.
        * The code below is based on FLOT's original html-driven logic, which I modified to get things done 
        * using the canvas context
        */
        
        /**
        * Adds the new text-related functions to the Flot canvas context (ctx)
        */
        function enableCanvasText(plot, ctx){
            var options = plot.getOptions();
            var placeholder = plot.getPlaceholder();            

            /**
            * Check if the user has requested canvas-based text support
            * If not, the HTML text is not removed from the web page
            */
            if (options.grid.canvasText.show) {
                CanvasTextFunctions.enable(ctx);
                ctx.fontFamily(options.grid.canvasText.font);
                if (options.grid.show) {
                    /**
                    * Remove any div-based tickLabels from the page
                    */              
                    placeholder.find(".tickLabel").remove();                
                    plot.insertLabelsCanvasText(ctx);
                }

                /**
                * Remove any table-based legendLabels from the page.
                * .remove() is not being used because we don't want to remove the TD element.
                * We want to maintain the original width to guarantee enough room for the new text.
                * Note that the canvas-based legend text is only drawn when a legend container is not provided.
                * Although FLOT's original implementation allows the legend to show up anywhere on the page,
                * this implementation (so far) only allows the legend to be created on the canvas context.
                */
                if (options.legend.container == null) { 
                    placeholder.find(".legendLabel").each(function(i,el) {
                        el = $(el);
                        elWidth = el.width();
                        el.text("");
                        el.width(elWidth);
                    });
                    placeholder.find(".legend").remove();
                    plot.insertLegendCanvasText(ctx);
                }

                if (options.grid.canvasText.series) {
                    plot.insertSeriesDataPointsCanvasText(ctx);
                }

            }
        }

        /**
        * This is the modified version of FLOT's insertLabels function.
        */  
        plot.insertLabelsCanvasText = function (ctx) {
            var options = plot.getOptions();
            var axes = plot.getAxes();
            var plotOffset = plot.getPlotOffset();
            var plotHeight = plot.height();
            var plotWidth = plot.width();

            ctx.strokeStyle = options.grid.color;
            
            function addLabels(axis, labelGenerator) {
                for (var i = 0; i < axis.ticks.length; ++i) {
                    var tick = axis.ticks[i];
                    if (!tick.label || tick.v < axis.min || tick.v > axis.max)
                        continue;
                    labelGenerator(tick, axis);
                }
            }

            var margin = options.grid.labelMargin + options.grid.borderWidth;

            addLabels(axes.xaxis, function (tick, axis) {
                var label = tick.label;
                var labels;

                /**
                * If user requests, tick labels are displayed one word per line
                */
                labels = (options.grid.canvasText.lineBreaks.show)?label.split(" "):[label];
                
                y = (plotOffset.top + plotHeight + margin); 
                if (labels.length > 1) {
                    y -= options.grid.canvasText.lineBreaks.marginBottom; // move up the labels a bit
                }
                for(var j=0; j < labels.length; j++){
                    labelWidth = ctx.measureText(labels[j]);
                    /**
                    * implements an equivalent to the text-align:center CSS option
                    */                                                  
                    x = Math.round(plotOffset.left + axis.p2c(tick.v) - labelWidth/2);
                    /**
                    * where:
                    *   plotOffset.left = area where the Y axis is plotted (left of the actual graph)
                    *   axis.p2c(tick.v) = # of pixels associated with the tick value
                    *   labelWidth/2 = half of the length of the label so it's centered 
                    */              
                    y += ctx.fontAscent();
                    /**
                    * where:
                    *   ctx.fontAscent() = height of the character
                    */              
                    ctx.fillText(labels[j],x,y);
                    y += options.grid.canvasText.lineBreaks.lineSpacing; // for line-spacing
                }
            });
            
            addLabels(axes.yaxis, function (tick, axis) {
                label = tick.label;
                labelWidth = ctx.measureText(label);
                labelHeight = ctx.fontAscent();
                plotOffsetLeftArea = plotOffset.left - margin;
        
                x = 0;

                /**
                * implements an equivalent to the text-align:right CSS option
                */                              
                x +=(Math.round(labelWidth) < plotOffsetLeftArea)?plotOffsetLeftArea-Math.round(labelWidth):0;
                x -=(Math.round(labelWidth) > plotOffsetLeftArea)?Math.round(labelWidth)-plotOffsetLeftArea:0;
                                                    
                y = Math.round(plotOffset.top + axis.p2c(tick.v) - labelHeight/2);
                y += ctx.fontAscent(); 

                ctx.fillText(label, x, y);
            }); 

            addLabels(axes.x2axis, function (tick, axis) {
                var label = tick.label;
                var labels;

                /**
                * If user requests, tick labels are displayed one word per line
                */
                labels = (options.grid.canvasText.lineBreaks.show)?label.split(" "):[label];
                
                y = (plotOffset.bottom); 

                if (labels.length > 1) {
                    y += options.grid.canvasText.lineBreaks.marginTop; // move up the labels down a bit
                }

                for(var j=0; j < labels.length; j++){
                    labelWidth = ctx.measureText(labels[j]);
                    /**
                    * implements an equivalent to the text-align:center CSS option
                    */                                                  
                    x = Math.round(plotOffset.left + axis.p2c(tick.v) - labelWidth/2);
                    /**
                    * where:
                    *   plotOffset.left = area where the Y axis is plotted (left of the actual graph)
                    *   axis.p2c(tick.v) = # of pixels associated with the tick value
                    *   labelWidth/2 = half of the length of the label so it's centered 
                    */              
                    y -= ctx.fontAscent();
                    /**
                    * where:
                    *   ctx.fontAscent() = height of the character
                    */              
                    ctx.fillText(labels[j],x,y);
                    y -= options.grid.canvasText.lineBreaks.lineSpacing; // for line-spacing
                }   
            });

            addLabels(axes.y2axis, function (tick, axis) {
                label = tick.label;
                labelWidth = ctx.measureText(label);
                labelHeight = ctx.fontAscent();

                /**
                * implements an equivalent to the text-align:left CSS option
                */                                              
                x = plotOffset.left + plotWidth + margin;
                                                    
                y = Math.round(plotOffset.top + axis.p2c(tick.v) - labelHeight/2); 
                y += ctx.fontAscent(); 

                ctx.fillText(label, x, y);
            });
        }

        /**
        * Plots the series values as labels on the graph
        */  
        plot.insertSeriesDataPointsCanvasText = function (ctx) {
            var options = plot.getOptions();
            var plotOffset = plot.getPlotOffset();
            var plotHeight = plot.height();
            var plotWidth = plot.width();
            var seriesData, o, contextObj, paramObj, resultObj;
            
            function defaultFormatFunction (contextObj, scopeObj){
                var r = {};
                r.leftOffset = 10;
                r.topOffset = -20;
                r.label = contextObj.y;
                r.color = "#grid";
                return r;
            }

            var margin = options.grid.labelMargin + options.grid.borderWidth;

            seriesOption = options.grid.canvasText.series;

            ctx.fontFamily(options.grid.canvasText.seriesFont);

            for (var i = 0; i < seriesOption.length; ++i) {
                    paramObj = null;
                    contextObj = {};
                    
                    if (typeof seriesOption[i] == "number") {
                        contextObj.series = seriesOption[i];
                        formatLabelFunction = defaultFormatFunction;
                    } else if (typeof seriesOption[i][0] == "number" && typeof seriesOption[i][1] == "function") {
                        contextObj.series = seriesOption[i][0];
                        formatLabelFunction = seriesOption[i][1];                       
                        if (typeof seriesOption[i][2] == "object") {
                            paramObj = seriesOption[i][2];
                        }                       
                    } else {
                        continue;
                    }
                    if (plot.getData()[contextObj.series]) {
                        seriesData = plot.getData()[contextObj.series].data;
                        var seriesLength = seriesData.length / 2;   // as each seriesData[j] object counts twice (x,y)                      
                        for (var j = 0; j < seriesLength; ++j) {
                             contextObj.index = j;
                             contextObj.x = seriesData[j][0];                           
                             contextObj.y = seriesData[j][1];                                                       
                             resultObj = formatLabelFunction(contextObj, paramObj);
                             resultObj.leftOffset = (resultObj.leftOffset)?resultObj.leftOffset:0;
                             resultObj.topOffset = (resultObj.topOffset)?resultObj.topOffset:0;
                             resultObj.label = (resultObj.label)?resultObj.label.toString():"";
                             if (resultObj.color) {
                                 if (resultObj.color=="#series") {
                                     resultObj.color = plot.getData()[contextObj.series].color;
                                 } else if (resultObj.color=="#grid") {
                                     resultObj.color = options.grid.color;
                                 } else {
                                     resultObj.color = resultObj.color;
                                 }
                             } else {
                                 resultObj.color = options.grid.color;
                             }
                             o = plot.pointOffset({x: contextObj.x, y: contextObj.y});
                             x = o.left  + margin + resultObj.leftOffset;
                             y = o.top + margin + resultObj.topOffset;                      
                             y += ctx.fontAscent();
                             ctx.strokeStyle = resultObj.color; 
                             ctx.fillText(resultObj.label,x,y);             
                        }
                    }   
                }
                ctx.fontFamily(options.grid.canvasText.font);
        }


        /**
        * This is the modified version of FLOT's insertLegend function.
        * All the N/E/W/S placements are currently supported
        * todo: add support to off-plot placement
        */  
        plot.insertLegendCanvasText = function (ctx) {
            var options = plot.getOptions();
            var series = plot.getData();
            var plotOffset = plot.getPlotOffset();
            var plotHeight = plot.height();
            var plotWidth = plot.width();
            
            if (!options.legend.show)
                return;

            var lf = options.legend.labelFormatter, s, label, legendWidth, legendHeight;

            legendWidth = 0;
            legendHeight = 0;
            
            /**
            * Calculates the width of the legend area
            */  
            for (var i = 0; i < series.length; ++i) {
                s = series[i];
                label = s.label;
                if (!label) {
                    continue;
                }
                if (lf) {
                    label = lf(label, s);
                }
                labelWidth = ctx.measureText(label);
                if (labelWidth > legendWidth) {
                    legendWidth = labelWidth
                }
            }
            
            /**
            * 22 is the width of the color boxes to the left of the series legend labels
            * 18 is the line-height of those boxes (i.e. series)
            */ 
            LEGEND_BOX_WIDTH = 22;
            LEGEND_BOX_LINE_HEIGHT = 18;
            legendWidth = legendWidth + LEGEND_BOX_WIDTH;
            legendHeight = (series.length * LEGEND_BOX_LINE_HEIGHT);
            
            var x, y;
            if (options.legend.container != null) {
                x = $(options.legend.container).offset().left;
                y = $(options.legend.container).offset().top;
            } else {
                var pos = "",
                    p = options.legend.position,
                    m = options.legend.margin;
                if (m[0] == null)
                    m = [m, m];
                if (p.charAt(0) == "n") {
                    y = Math.round(plotOffset.top + options.grid.borderWidth + m[1]);
                } else if (p.charAt(0) == "s") {
                    y = Math.round(plotOffset.top + options.grid.borderWidth + plotHeight - m[0] - legendHeight);
                }
                if (p.charAt(1) == "e") {
                    x = Math.round(plotOffset.left + options.grid.borderWidth + plotWidth - m[0] - legendWidth); 
                } else if (p.charAt(1) == "w") {
                    x = Math.round(plotOffset.left + options.grid.borderWidth + m[0]);
                }

                if (options.legend.backgroundOpacity != 0.0) {
                    var c = options.legend.backgroundColor;
                    if (c == null) {
                        c = options.grid.backgroundColor;
                    }
                    if (c && typeof c == "string") {
                        ctx.globalAlpha = options.legend.backgroundOpacity; 
                        ctx.fillStyle = c;
                        ctx.fillRect(x,y,legendWidth,legendHeight);
                        ctx.globalAlpha = 1.0;                              
                    }                       
                }   
            }

            var posx, posy;
            for (var i = 0; i < series.length; ++i) {
                s = series[i];
                label = s.label;
                if (!label) {
                    continue;
                }

                if (lf) {
                    label = lf(label, s);
                }
                
                posy = y + (i * 18);
                ctx.fillStyle = options.legend.labelBoxBorderColor;                 
                ctx.fillRect(x,posy,18,14);  
                // ctx.clearRect(x+1,posy+1,16,12); // It turns out ExplorerCanvas doesn't handle clearRect() very well (issue #20)
                ctx.fillStyle = "#FFF";             // Since I just wanted to mirror the look and feel of the HTML version               
                ctx.fillRect(x+1,posy+1,16,12);     // I opted for just using a white rectangle here

                ctx.fillStyle = s.color;                    
                ctx.fillRect(x+2,posy+2,14,10);

                posx = x + 22;
                posy = posy + ctx.fontAscent() + 2;
                                    
                ctx.fillText(label, posx, posy);
            }
        }

        /**
        * Adds hook to enable this plugin's logic shortly after drawing the whole graph
        */  
        plot.hooks.draw.push(enableCanvasText);
    }
    
    $.plot.plugins.push({
        init: init,
        options: options,
        name: 'flot.canvas.text',
        version: '0.1'
    });
})(jQuery);

