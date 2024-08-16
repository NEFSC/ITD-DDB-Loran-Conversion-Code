<?php


class conversionHelper {
    public float $decLat;
    public float $decLon;
    public string $latString;
    public string $lonString;
    public string $lat;
    public string $lon;

    public function __construct(string $lat, string $lon, string $inputFormat, string $outputFormat) {
        $this->lat = $lat;
        $this->lon = $lon;

        if ($lat == '' || $lon == '') {
            $this->decLat = 0.0;
            $this->decLon = 0.0;
            $this->latString = '';
            $this->lonString = '';
            return;
        }
        // Converts real world DMS to D.MS (the Navy BASIC program format)
        if ($inputFormat == 'DMS' && $outputFormat == 'D.MS') {
            $latLonVal = $this->validateDdMmSsLatLon($lat, $lon);
            $lat = $latLonVal[0];
            $lon = $latLonVal[1];
            $result = $this->convertDegDotMinSecToLl($lat, $lon);
            $this->latString = $result['lat'];
            $this->lonString = $result['lon'];
            $this->lat = floatval($result['lat']);
            $this->lon = floatval($result['lon']);
        }
        // converts real world DMS to decimal degrees
        else if ($inputFormat == 'DMS' && $outputFormat == 'dec') {
            $result = $this->convertDMStoDec($lat, $lon);
            $this->lat = $result['lat'];
            $this->lon = $result['lon'];
            $this->latString = (string) $result['lat'];
            $this->lonString = (string) $result['lon'];
        }
        // Converts real world DMS to degrees and decimal minutes
        else if ($inputFormat == 'DMS' && $outputFormat == 'DMm') {
            $result = $this->convertDMStoDMm($lat, $lon);
            $this->latString = (string) $result['lat'];
            $this->lonString = (string) $result['lon'];
        }
        else {
            $this->latString = trim($lat);
            $this->lonString = trim($lon);
        }
    }

    /* This function converts the standard lat/lon format to the DD.MMSS format the old LORAN BASIC uses.
    Example: "46 48 27.305 N" and "67 55 37.159 W" becomes "46.807584N" and "67.926989W"
    NOTE: In the DD.MMSS format used in the BASIC program, West longitude is positive and East is negative,
    so the sign of the longitude is reversed from the standard format. */
    public function convertDegDotMinSecToLl($lat, $lon) {
        $resultArray = array();
        $latArray = explode(" ", $lat);
        $lonArray = explode(" ", $lon);

        $latDotFormat = $latArray[0] . '.' . $latArray[1] . str_replace('.', '', $latArray[2]) . ' ' . $latArray[3];
        //$latDotFormat = $latArray[3] == 'N' ? $latDotFormat : '-' . $latDotFormat;

        $lonDotFormat = $lonArray[0] . '.' . $lonArray[1] . str_replace('.', '', $lonArray[2]) . ' ' . $lonArray[3];
        //$lonDotFormat = $lonArray[3] == 'W' ? $lonDotFormat : $lonDotFormat;

        $resultArray = array('lat' => $latDotFormat, 'lon' => $lonDotFormat);
        return $resultArray;
    }

    /* This method rounds seconds if the ROUNDSECS constant is set to true,
     * and it adds the N/S and E/W designations if +/- is used instead (which shouldn't happen, but just in case...)
     * Caution: as noted elsewhere, the Navy BASIC program designates West longitude as positive and East as negative,,
     * so the sign of the longitude is reversed from the standard format, but here we are processing Lat/Lon in the standard format.
     */
    public function validateDdMmSsLatLon($lat, $lon) {
        $latArray = explode(" ", trim($lat));
        $lonArray = explode(" ", trim($lon));
        $latSecInt = intval($latArray[2]);

        //$secs = $_ENV['app.ROUNDSECS'] == true ? intval($latSecInt + 0.5) : $latSecInt;
        $secs = $latSecInt;

        // check for N or S, else + or -
        if (count($latArray) != 4) {
            $dir = intval($latArray[0]) < 1 ? 'S' : 'N';
            $lat = $latArray[0] . ' ' . $latArray[1] . ' ' . $secs . ' ' . $dir;
        }
        if (count($lonArray) != 4) {
            $dir = intval($lonArray[0]) < 1 ? 'W' : 'E';
            $lon = $lonArray[0] . ' ' . $lonArray[1] . ' ' . $secs . ' ' . $dir;
        }
        $return = array($lat, $lon);
        return $return;
    }

    /* Convert to decimal degrees
     * This converts the absolute values of the Lat and Lon to decimal degrees,
     * so the N/S and E/W or +/- designations are not relevant here
     * 
     * @param string $lat
     * @param string $lon
     * 
     * @return array
     */
    public function convertDMStoDec($lat, $lon) {
        $latArray = explode(" ", trim($lat));
        $lonArray = explode(" ", trim($lon));

        if(count($latArray) < 3 || count($lonArray) < 3) {
            $la = 'lalala';
        }
        $lat0Num = floatval($latArray[0]);
        $lon0Num = floatval($lonArray[0]);
        $lat1Num = floatval($latArray[1]);
        $lon1Num = floatval($lonArray[1]);
        $lat2Num = floatval($latArray[2]);
        $lon2Num = floatval($lonArray[2]);

        $latDecimal = $lat0Num + round(($lat1Num / 60) + ($lat2Num / 3600), 5);
        $lonDecimal = $lon0Num + round(($lon1Num / 60) + ($lon2Num / 3600), 5);

        $resultArray = array('lat' => $latDecimal, 'lon' => $lonDecimal);
        return $resultArray;
    }

    /* Convert to degrees and decimal minutes
     * This converts the absolute values of the Lat and Lon to decimal minutes,
     * so the N/S and E/W or +/- designations are not relevant here
     * 
     * @param string $lat
     * @param string $lon
     * 
     * @return array
     */
    public function convertDMStoDMm($lat, $lon) {
        $latArray = explode(" ", trim($lat));
        $lonArray = explode(" ", trim($lon));

        $lat0Num = floatval($latArray[0]);
        $lon0Num = floatval($lonArray[0]);
        $lat1Num = floatval($latArray[1]);
        $lon1Num = floatval($lonArray[1]);
        $lat2Num = floatval($latArray[2]);
        $lon2Num = floatval($lonArray[2]);

        $latMm = (string) $lat0Num . " " . (string) ($lat1Num + round(($lat2Num / 60), 3));
        $lonMm = (string) $lon0Num . " " . (string) ($lon1Num + round(($lon2Num / 60), 3));

        $resultArray = array('lat' => $latMm, 'lon' => $lonMm);
        return $resultArray;
    }
}