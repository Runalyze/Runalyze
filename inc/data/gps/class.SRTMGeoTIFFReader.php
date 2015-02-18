<?php
/**
 * This file contains class::SRTMGeoTIFFReader
 * @package Runalyze\Data\GPS\Elevation
 */
/**
 * Reader for GeoTIFF files
 * 
 * This class is originally written by Bob Osola, http://www.osola.org.uk/elevations/index.htm
 * We made some minor changes to fit this class into our architecture.
 * 
 * @see http://gis.stackexchange.com/questions/19589/how-to-read-geotiff-using-php
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Data\GPS\Elevation
 */
class SRTMGeoTIFFReader {
	/**
	*  Returns elevation in metres from CGIAR-CSI SRTM v4 GeoTIFF files given WGS84 latitude and Longitude
	* 
	*  Data points are available for each 3" of arc (approx every 90m)
	*  Each data file covers a 5 degree x 5 degree area of the world's surface between 60N & 60S
	*  Files are named in the range 'srtm_01_01.tif' to 'srtm_72_24.tif'
	* 
	*  See http://srtm.csi.cgiar.org for more info
	*/
	// the number of bytes required to hold a TIFF offset address
	const LEN_OFFSET = 4;

	// CGIAR-CSI SRTM GeoTIFF constants
	const NUM_DATA_ROWS = 6000;     // the number of data rows in the file ( = ImageLength tag value)
	const NUM_DATA_COLS = 6000;     // the number of data columns in the file ( = ImageWidth tag value)
	const DEGREES_PER_TILE = 5;     // each tile is 5 x 5 degrees of lat/lon
	const PIXEL_DIST = 0.000833333; // the distance represented by one pixel (0 degrees 0 mins 3 secs of arc = 1/1200)
	const STRIPOFFSETS = 0x44bbd5c; // the offset address of the 'StripOffsets' tag

	const NO_DATA = 0;          // Not from original code, don't know what to set here

	// read/write public properties
	public $showErrors = true;     // show messages on error condition, otherwise dies silently
	public $maxPoints = 5000;      // default maximum number of multiple locations accepted

	// private properties      
	private $dataDir;              // path to local directory containing the GeoTIFF data files
	private $fileName;             // name of current GeoTIFF data file
	private $fp;                   // file pointer to current GeoTIFF data file 
	private $tileRefHoriz;         // the horizontal tile reference figure (01-72)
	private $tileRefVert;          // the vertical tile reference figure (01-24)
	private $latLons = Array();    // the supplied lats & lons
	private $elevations = Array(); // the elevations values found

	/**
	* Constructor: assigns data directory
	* @param mixed $dataDir
	* @return SRTMGeoTIFFReader
	*/
	function __construct($dataDir) {
		$this->dataDir = $dataDir;
	}

	/**
	* Destructor: clean up resources
	*/
	function __destruct() {
		if (is_resource($this->fp)) {
			fclose($this->fp);
		}
	}

	/**
	* Returns the current file name
	*/
	public function getFileName() {
		return $this->fileName;
	}

	/**
	* Returns the number of elevations calculated
	*/
	public function getNumElevations() {
		return count($this->elevations);
	}

	/**
	* Returns an array of total ascent & descent
	*/
	public function getAscentDescent() {
		$ascent = $descent = 0;
		$numElevations = $this->getNumElevations();

		if ($numElevations > 1) {
			for ($i = 1; $i < $numElevations; $i++) {
				$thisElev = $this->elevations[$i];
				$lastElev = $this->elevations[$i-1];
				$diff = abs($lastElev - $thisElev);

				if ($diff > 0) {
					($thisElev > $lastElev) ? $ascent += $diff : $descent += $diff; 
				}
			}
		}

		return array("ascent" => $ascent, "descent" => $descent);
	}

	/**
	* Returns the total distance
	* 
	*/
	public function getTotalDistance() {
		for ($i = 2; $i < count($this->latLons); $i += 2) {
			$distance += $this->getDistance(
				$this->latLons[$i-2], 
				$this->latLons[$i-1], 
				$this->latLons[$i], 
				$this->latLons[$i+1],
				false
			);
		}

		return $distance;
	}

	/**
	* Returns the elevation in metres for a given Latitude and Longitude
	* where N & E are positive and S & W are negative
	* e.g. Lat 55?30'N, Lon 002?20'W is entered as (55.5, -2.333333)
	* 
	* Set optional parameter Sinterpolate to true to return a more accurate but slower calculation
	* 
	* @param float $latitude
	* @param float $longitude
	* @param bool $interpolate
	*/
	public function getElevation($latitude, $longitude, $interpolate = false) {
		// work out the data tile name
		if (! $this->checkTileInfo($latitude, $longitude)) {
			// it's not the same tile as the last run, so get the new tile and file name
			$fileName = $this->getTileFileName ($this->tileRefHoriz, $this->tileRefVert);
			// read the file and jump to the first data address
			$this->getSRTMFilePointer($fileName);
		}

		if ($interpolate) {
			// use more accurate but slower bilinear iterpolation method
			$elevation = $this->getInterpolatedElevation($latitude, $longitude);
		}
		else {
			// use less accurate but faster rounding method
			$elevation = $this->getRoundedElevation($latitude, $longitude);
		} 

		return $elevation;
	}

	/**
	* Returns an array of elevations in metres given an array of lats & lons
	* as {lat1, lon1, ... latn, lonn}. Can optionally calculate intermediate locations at
	* 3" intervals and optionally use bilinear interpolation 
	* 
	* @param string $latLons
	* @param bool $addIntermediatelatLons
	* @param bool $interpolate
	*/
	public function getMultipleElevations($latLons, $addIntermediatelatLons = false, $interpolate = false) {
		$numlatLons = count($latLons);

		//if ($numlatLons < 4) {
		//	$this->handleError(__METHOD__ , "need at least two point locations in the latLons array");
		//}

		// bale out if limit is reached
		$limit = $this->maxPoints;
		if (($numlatLons / 2) > $limit) {
			$this->handleError(__METHOD__ , "maximum number of allowed point locations ($limit) exceeded");
		} 

		if (($numlatLons % 2) != 0 ) {
			$this->handleError(__METHOD__ , "uneven number of lat and lon params ");
		}

		if ($addIntermediatelatLons) {
			// work out intermediate lats and lons for every 3" of arc
			for ($i = 2; $i < $numlatLons; $i += 2) {

				$startLat = $latLons[$i-2];
				$endLat =  $latLons[$i]; 
				$dlat =  $endLat - $startLat;

				$startLon = $latLons[$i-1];
				$endLon =  $latLons[$i+1];
				$dlon = $endLon - $startLon;

				(Abs($dlat) >= Abs($dlon)) ? 
					$numSteps = floor(Abs($dlat) / self::PIXEL_DIST):
					$numSteps = floor(Abs($dlon) / self::PIXEL_DIST);

				// calculate approximate intermediate positions for each 3" 
				// by simple proportion of dlat and dlon - assumes flat earth!
				$totNumSteps += $numSteps;
				if  ($totNumSteps >= $limit) {
					 $this->handleError(__METHOD__ , "maximum number of allowed point locations ($limit) exceeded while calculating intermediate points");
				}

				for ($j = 0; $j < $numSteps; $j++) {
					$midLat = $startLat + ($j * $dlat / $numSteps);
					$midLon = $startLon + ($j * $dlon / $numSteps);
					$elevations[] = $this->getElevation($midLat, $midLon, $interpolate);
				}
			}
			$elevations[] = $this->getElevation($endLat, $endLon, $interpolate);
		}

		else {
			// just do the provided lats and lons, no intermediate positions are calculated
			for ($k = 0; $k < $numlatLons; $k += 2) {
				$elevations[] = $this->getElevation($latLons[$k], $latLons[$k+1], $interpolate);
			}
		}

		$this->elevations = $elevations;
		$this->latLons = $latLons;

		return $elevations;
	}

	/**
	* Returns the elevation in metres for a given tile and zero-based row and column
	* e.g. for 7th row, 4th col in 'srtm_36_02.tif', call getElevationByRowCol(36, 2, 6, 3)
	* Used only for testing, sanity checking and comparing to ASCII version of data
	* 
	* @param int horizTileRef
	* @param int vertTileRef
	* @param int row
	* @param int col
	*/
	public function getElevationByRowCol($tileRefHoriz, $tileRefVert, $row, $col) {
		if (($row < 0) || ($row > (self::NUM_DATA_ROWS -1))) {
			$this->handleError(__METHOD__ , "data row number $row out of range");
		}
		if (($col < 0) || ($col > (self::NUM_DATA_COLS -1))) {
			$this->handleError(__METHOD__ , "data column number $col out of range");
		}

		// get the data file name and the lat & long values in the top left-hand corner
		// then read the file and jump to the first data address
		$fileName = $this->getTileFileName ($tileRefHoriz, $tileRefVert);
		$this->getSRTMFilePointer($fileName);

		// get the elevation for the given row & column
		$elevation = $this->getRowColumnData($row, $col);
		if (!$elevation) {
			$elevation = self::NO_DATA;
		}
		return "Row: $row, Col: $col. Elevation: $elevation m<br>";  
	}

	/**
	* Checks the SRTM file is a valid TIFF and find the data location by lookup.
	* 
	* Only used for getting the StripOffsets address if for some reason it's different from
	* the address used by all the six UK SRTM files 
	* 
	* @param mixed $fileName
	*/
	public function checkSRTMfile ($fileName) {
		// standard TIFF constants
		$TIFF_ID = 42;             // magic number located at bytes 2-3 which identifies a TIFF file
		$TAG_STRIPOFFSETS = 273;   // identifying code for 'StripOffsets' tag in the Image File Directory (IFD)
		$LEN_IFD_FIELD = 12;       // the number of bytes in each IFD entry
		$LEN_OFFSET = 4;           // the number of bytes required to hold a TIFF offset address
		$BIG_ENDIAN = "MM";        // byte order identifiers located at bytes 0-1
		$LITTLE_ENDIAN = "II";

		$filepath = $this->dataDir . "/". $fileName;
		if (!file_exists($filepath)){
			$this->handleError(__METHOD__ , "the file '$filepath' does not exist");
		}
		$fp = fopen($filepath, 'rb');
		if ($fp === false) {
			$this->handleError(__METHOD__ , "could not open the file '$filepath'");
		} 

		// go to the file header and work out the byte order (bytes 0-1)
		// and TIFF identifier (bytes 2-3)
		fseek($fp, 0);
		$dataBytes = fread($fp, 4);
		$data = unpack('c2chars/vTIFF_ID', $dataBytes);

		// check it's a valid TIFF file by looking for the magic number
		$TIFF = $data['TIFF_ID'];
		if ($TIFF != $TIFF_ID) {
			$this->handleError(__METHOD__ , "the file '$fileName' is not a valid TIFF file");
		} 

		// convert the byte order code to ASCII to get Motorola or Intel ordering identifiers
		$byteOrder = sprintf('%c%c', $data['chars1'], $data['chars2']);

		// the remaining 4 bytes in the header are the offset to the IFD
		fseek($fp, 4);
		$dataBytes = fread($fp, 4);
		// unpack in whichever byte order was identified previously
		// - this seems to be always 'II' but whether this is always the case is not specified
		// so we do the check each time to make sure
		if ($byteOrder == $LITTLE_ENDIAN) { 
			$data = unpack('VIFDoffset', $dataBytes); 
		}
		elseif ($byteOrder == $BIG_ENDIAN){
			$data = unpack('NIFDoffset', $dataBytes);
		}
		else {
			self::handleError(__METHOD__ , "could not determine the byte order of the file '$fileName'");
		}

		// now jump to the IFD offset and get the number of entries in the IFD
		// which is always stored in the first two bytes of the IFD
		fseek($fp, $data['IFDoffset']);
		$dataBytes = fread($fp, 2) ;
		$data = ($byteOrder == $LITTLE_ENDIAN) ? 
			unpack('vcount', $dataBytes) :
			unpack('ncount', $dataBytes);
		$numFields = $data['count'];

		// iterate the IFD entries until we find the 'StripOffsets' entry
		for ($i = 0; $i < $numFields; $i++) {
			$dataBytes = fread($fp, $LEN_IFD_FIELD);
			$data = ($byteOrder == $LITTLE_ENDIAN) ?
				unpack('vtag/vtype/Vcount/Voffset', $dataBytes):
				unpack('ntag/ntype/Ncount/Noffset', $dataBytes);
			if ($data['tag'] == $TAG_STRIPOFFSETS) {
				$offset = $data['offset'];
				break;
			}
		}

		if (!$offset) {
			self::handleError(__METHOD__ , "could not find the 'StripOffsets' entry in the TIFF IFD for $fileName");
		}

		// check 'StripOffsets' contains the correct amount of data rows for CGIAR-CSI SRTM GeoTIFF files
		if ($data['count'] != self::NUM_DATA_ROWS) {
			self::handleError(__METHOD__ , "incorrect number of data rows in '$fileName'");
		}

		echo "<p>StripOffsets location is: $offset (0x" . sprintf('%x', $offset) . ")</p>";
	}

	/**
	* Returns the elevation value of the single data point which is closest to the parameter point
	* 
	* @param float $latitude
	* @param float $longitude
	*/
	private function getRoundedElevation($latitude, $longitude) {
		// Returns results exactly as per http://www.geonames.org/export/web-services.html#srtm3
		// NB: we ignore row and col 6001 as these as these are overlaps onto the adjacent tiles
		// on the S and E sides of the tile
		$row = round(($this->topleftLat - $latitude) / self::DEGREES_PER_TILE * (self::NUM_DATA_ROWS - 1));
		$col = round(abs($this->topleftLon - $longitude) / self::DEGREES_PER_TILE * (self::NUM_DATA_COLS - 1));

		// get the elevation for the calculated row & column
		return $this->getRowColumnData($row, $col);
	}

	/**
	* Returns the elevation of the parameter point by performing a bilinear interpolation
	* of the elevation values of the four data points which surround the parameter point
	* 
	* @param float $latitude
	* @param float $longitude
	*/
	private function getInterpolatedElevation($latitude, $longitude) {
		// calculate row & col for the data point p0 (above & left of the parameter point)
		// HINT BY HC: Can now use all rows, not!?
		//$row[0] = floor(($this->topleftLat - $latitude) / self::DEGREES_PER_TILE * (self::NUM_DATA_ROWS -1));
		//$col[0] = floor(abs($this->topleftLon - $longitude) / self::DEGREES_PER_TILE * (self::NUM_DATA_COLS -1));
		$row[0] = floor( abs($this->topleftLat - $latitude) / self::PIXEL_DIST );
		$col[0] = floor( abs($this->topleftLon - $longitude) / self::PIXEL_DIST );

		// set row & col for the data point p1 (above & right of the parameter point)
		$row[1] =  $row[0];
		$col[1] = $col[0] + 1;

		// set row & col for the data point p2 (below & left of the parameter point) 
		$row[2] =  $row[0] + 1;
		$col[2] = $col[0];   

		// set row & col for the data point p3 (below & right of the parameter point)
		$row[3] =  $row[0] + 1;
		$col[3] = $col[0] + 1; 

		// get the difference in lat & lon between the p0 data point and the parameter point
		// HINT BY HC: This original calculation seems to be buggy
		//$dlat = $this->topleftLat - ($row[0] * self::PIXEL_DIST) - abs($latitude);
		//$dlon = $this->topleftLon - ($col[0] * self::PIXEL_DIST) - abs($longitude);
		$dlat = abs($this->topleftLat - $latitude) - ($row[0] * self::PIXEL_DIST);
		$dlon = abs($this->topleftLon - $longitude) - ($col[0] * self::PIXEL_DIST);

		// express dlat & dlon as a proportion of the side of the square created by p0, p1, p2, p3
		$dlatProportion = abs($dlat / self::PIXEL_DIST);
		$dlonProportion = abs($dlon / self::PIXEL_DIST);

		// get the elevation values for points p0, p1, p2 & p3
		$noData = false;
		for ($i = 0; $i < 4; $i++) {
			$elev = $this->getRowColumnData($row[$i], $col[$i]);
			if ($elev == self::NO_DATA) {
				$noData = true;
			}
			$points[] = $elev;
		}

		// interpolate between the four elevation values
		if (!$noData) {
			$elevation = self::interpolate ($dlonProportion, $dlatProportion, $points);
		} else {
			$elevation = self::NO_DATA;
		}

		return $elevation;
	}

	/**
	* Returns the value for point P located inside the square formed by four data points
	* by performing a bilinear interpolation of the four data values
	* 
	* @param float $x
	* @param float $y
	* @param array $pointData
	*/
	private function interpolate ($x, $y, $pointData) {
		// NB: x & y are expressed as a proportions of the dimension of the square side
		// p0------------p1   
		// |      |
		// |      y
		// |      | 
		// |--x-- .P 
		// |
		// p2------------p3
		// bilinear interpolation formula
		$val = $pointData[0] * (1 - $x) * (1 - $y) +
			$pointData[1] * $x * (1 - $y) +
			$pointData[2] * $y * (1 - $x) +
			$pointData[3] * $x * $y;

		return round($val);
	}

	/** 
	* Saves the horizontal & vertical tile identifer numbers plus lat & long values
	* of the the top left-hand corner of the tile to class vars
	* 
	* Returns true if the current tile is the same as the last-used tile
	* 
	* @param float $lat
	* @param float $lon
	*/
	private function checkTileInfo($lat, $lon) {
		$MAX_LAT = 60; // maximum N & S latitude for which data is available

		// NB: gets the values of the top left lat and lon (row 0, col 0)
		if (($lat > - $MAX_LAT) && ($lat <= $MAX_LAT)) {
			$tileRefVert = (fmod($lat, self::DEGREES_PER_TILE) == 0) ?
				(($MAX_LAT - $lat) / self::DEGREES_PER_TILE + 1) :
				(ceil(($MAX_LAT - $lat) / self::DEGREES_PER_TILE));

			$topleftLat = $MAX_LAT - (($tileRefVert -1) * self::DEGREES_PER_TILE);

			if ($lat < 0) {
				$topleftLat = -$topleftLat;
			}
		} else {
			$this->handleError(__METHOD__ , "latitude ($lat) out of range");
		}

		if (($lon > - 180) && ($lon < 180)) {
			$tileRefHoriz = (fmod($lon, self::DEGREES_PER_TILE) == 0) ?
				((180 + $lon) / self::DEGREES_PER_TILE + 1) :
				(ceil((180 + $lon) / self::DEGREES_PER_TILE));

			$topleftLon = (($tileRefHoriz -1) * self::DEGREES_PER_TILE) - 180;
		} else {
			$this->handleError(__METHOD__ , "longitude ($lon) out of range");
		}

		if (($this->tileRefHoriz == $tileRefHoriz) && ($this->tileRefVert == $tileRefVert)) {
			$sameFile = true;
		} else {
			$sameFile = false;
		}

		$this->tileRefHoriz = $tileRefHoriz;
		$this->tileRefVert = $tileRefVert;
		$this->topleftLat = $topleftLat;
		$this->topleftLon = $topleftLon;

		return $sameFile;
	}

	/**
	* Returns a file name given the vertical and horizontal identifiers
	* in the format used by CGIAR-CSI SRTM GeoTIFF v4, e.g. 'srtm_hh_vv.tif'
	* 
	* @param int $tileRefHoriz
	* @param int $tileRefVert
	*/
	private function getTileFileName($tileRefHoriz, $tileRefVert) {
		$fileName = "srtm_" 
				. str_pad($tileRefHoriz, 2, "0", STR_PAD_LEFT) 
				. "_" 
				. str_pad($tileRefVert, 2, "0", STR_PAD_LEFT) 
				. ".tif";

		return $fileName;
	}

	/**
	* Read the data file and get a pointer to the first data offset
	* 
	* @param string $fileName
	*/
	private function getSRTMFilePointer($fileName) {
		// close any previous file pointer
		if ($this->fp) {
			fclose($this->fp);
		}

		$filepath = $this->dataDir . "/". $fileName;
		if (!file_exists($filepath)){
			$this->handleError(__METHOD__ , "the file '$filepath' does not exist");
		}

		$fp = fopen($filepath, 'rb');
		if ($fp === false) {
			$this->handleError(__METHOD__ , "could not open the file '$filepath'");
		}

		// first data offset
		fseek($fp, self::STRIPOFFSETS);
		$this->fileName = $fileName;
		$this->fp = $fp;
	}

	/**
	* Returns the elevation data at a given zero-based row and column
	* using the current file pointer
	* 
	* @param int $row
	* @param int $col
	*/
	private function getRowColumnData($row, $col) {
		$DATA_VOID = 0x8000;       // data void ( = signed int -32768)
		$LEN_DATA = 2;             // the number of bytes containing each item of elevation data 
								   // ( = BitsPerSample tag value / 8) 

		// find the location of the required data row in the StripOffsets data
		$dataOffset = self::STRIPOFFSETS + ($row * self::LEN_OFFSET);
		fseek($this->fp, $dataOffset);
		$dataBytes = fread($this->fp, self::LEN_OFFSET);
		$data = unpack('VdataOffset', $dataBytes);

		// this is the offset of the 1st column in the required data row
		$firstColOffset = $data['dataOffset'];

		// now work out the required column offset relative to the 1st column
		$requiredColOffset = $col * $LEN_DATA;

		// combine the two and read the elevation data at that address 
		fseek($this->fp, $firstColOffset + $requiredColOffset);
		$dataBytes = fread($this->fp, $LEN_DATA);
		$data = unpack('velevation', $dataBytes);

		$elevation = $data['elevation'];
		if ($elevation == $DATA_VOID) {
			$elevation = 0;
		}

		return $elevation;
	}

	/**
	* Retruns the distance between two location using the Haversine formula
	* in either miles or kilometres
	* 
	* @param float $lat1
	* @param float $lon1
	* @param float $lat2
	* @param float $lon2
	* @param mixed $miles
	* @return float
	*/
	private function getDistance($lat1, $lon1, $lat2, $lon2, $miles=true){
		// earth's diameter in miles and kilometres
		$miles ? $earth = 3960 :  $earth = 6371;

		$lat1 = deg2rad($lat1);
		$lon1= deg2rad($lon1);

		$lat2 = deg2rad($lat2);
		$lon2 = deg2rad($lon2);

		$dlon = $lon2 - $lon1;
		$dlat = $lat2 - $lat1;

		// Haversine Formula  
		$sinlat = sin($dlat / 2);
		$sinlon = sin($dlon / 2);
		$a = ($sinlat * $sinlat) + cos($lat1) * cos($lat2) * ($sinlon * $sinlon);
		$c = 2 * asin(min(1, sqrt($a)));
		$d = $earth * $c;

		return $d;
	}

	/**
	* Error handler
	* 
	* @param string $error
	*/
	private function handleError($method, $message) {
		throw new Exception('SRTMGeoTIFFReader::'.$method.' fails: '.$message);
	}
}