<?php
require_once 'chainModel.php';
require_once 'loranEngine.php';
require_once 'conversionHelper.php';

class LoranController
{
    private $model;
    private $loranEngine;
    private $chains;
    private $td1;
    private $td2;
    private $gri;
    private $llFormat;

    public function __construct($id1, $id2, $gri, $llFormat)
    {
        //Float values may be passed in but they will be converted to int in LoranEngine.php, so we need to round them up first
        $id1 = round(floatval($id1));
        $id2 = round(floatval($id2));

        $this->td1 = $id1;
        $this->td2 = $id2;
        $this->gri = $gri;
        $this->llFormat = $llFormat;
        $this->model = new chainModel();
        $this->loadChains();
        $this->loranEngine = new LoranEngine($this->chains);
    }

    /* This method loads the chain data from the chainData JSON file. */
    private function loadChains()
    {
        $chainModel = new chainModel();
        $this->chains = $chainModel->getChains();
        // convert real-world chain data to the format needed by the LoranEngine
        $returnArray = [];
        foreach ($this->chains as $GRI => $chainMemberData) {
            $latLon = new conversionHelper($chainMemberData['LAT'], $chainMemberData['LON'], 'DMS', 'D.MS');
            $returnArray[$GRI]['lat'] = $latLon->lat;
            $returnArray[$GRI]['long'] = $latLon->lon;
            foreach ($chainMemberData['SLAVES'] as $k => $slaveData) {
                $slave = $slaveData['SLAVE_LETTER'];
                $slaveLatLon = new conversionHelper($slaveData['LAT'], $slaveData['LON'], 'DMS', 'D.MS');
                $returnArray[$GRI][$slave] = array(
                    'lat' => $slaveLatLon->lat,
                    'long' => $slaveLatLon->lon,
                    'delay' => $slaveData['DELAY']
                );
            }
        }
        $this->chains = $returnArray;
    }

    public function convert()
    {
        $td1 = $this->td1;//$this->model->convertToSeconds($this->td1);
        $td2 = $this->td2;//$this->model->convertToSeconds($this->td2);
        $gri = $this->gri;//$this->model->convertToSeconds($this->gri);

        // $altSolution refers to the two hyperbolic coordinates that are calculated by the LoranEngine.php class.
        // We want to find both of them and double check that we're getting the one that's closer to the master tower.
        $altSolution = [-1, 1];
        foreach ($altSolution as $a) {
            $result = $this->loranEngine->convertLoranToLL($gri, $td1, $td2, $a);
            if (is_string($result) && strpos($result, 'ERROR')) {
                $errMsg = new stdClass();

                $errMsg->error = 'Missing or incorrect values submitted';
                die(json_encode($errMsg));
            }
            if ($result['targetLocation']['lat'] == null || $result['targetLocation']['lon'] == null) {
                continue;
            }

            $testLatLon = new conversionHelper($result['targetLocation']['lat'], $result['targetLocation']['lon'], 'DMS', 'dec');

            if ($this->chains[$gri]['long'] == null || $this->chains[$gri]['long'] == null) {
                continue;
            }
            
            $towerLatLon = $this->chains[$gri];//new conversionHelper($this->chains[$gri]['long'], $this->chains[$gri]['long'], 'DMS', 'dec');
            $distanceFromMaster = $this->getDistanceFromMaster($testLatLon->lat, $testLatLon->lon, $towerLatLon['lat'], $towerLatLon['long']);

            $return[$a] = [];
            $latLon = new conversionHelper($result['targetLocation']['lat'], $result['targetLocation']['lon'], 'DMS', $this->llFormat);
            // convert +/- Lat and Lon to N/S and E/W
            $latStringArray = explode(' ', $latLon->latString);
            $lonStringArray = explode(' ', $latLon->lonString);
            if ($this->llFormat == 'DMS') {

                $latDir = $latStringArray[0] < 1 ? 'S' : 'N';
                // a negative Lon would normally be W, but it's E in the Loran Engine
                $lonDir = $lonStringArray[0] < 1 ? 'E' : 'W';

                $latLon->latString = str_replace('-', '', $latStringArray[0]) . ' ' . $latStringArray[1] . ' ' . $latStringArray[2] . ' ' . $latDir;
                $latLon->lonString = str_replace('-', '', $lonStringArray[0]) . ' ' . $lonStringArray[1] . ' ' . $lonStringArray[2] . ' ' . $lonDir;
                $return[$a]['targetLocation'] = array('latitude' => $latLon->latString, 'longitude' => $latLon->lonString);
            }
            else if ($this->llFormat == 'DMm' || $this->llFormat == 'dec') {
                $latDir = $latLon->latString < 1 ? 'S' : 'N';
                // a negative Lon would normally be W, but it's E in the Loran Engine
                $lonDir = $latLon->lonString < 1 ? 'E' : 'W';

                $latLon->latString = str_replace('-', '', $latLon->latString) . ' ' . $latDir;
                $latLon->lonString = str_replace('-', '', $latLon->lonString) . ' ' . $lonDir;
                $return[$a]['targetLocation'] = array('latitude' => $latLon->latString, 'longitude' => $latLon->lonString);
            }
            $return[$a]['distanceFromMaster'] = $distanceFromMaster;

        }

        // Return the solution that is closest to the master tower
        $locationPossibilitiesArray = [];
        foreach ($return as $key => $value) {
            $distIntKey = intval($value['distanceFromMaster']);
            $locationPossibilitiesArray[$distIntKey] = $value;
        }
        ksort($locationPossibilitiesArray);
        $return = array_shift($locationPossibilitiesArray);

        unset($return['distanceFromMaster']);
        $success = array('success' => $return);
        return json_encode($success);
    }


    private function getDistanceFromMaster($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo) {
        $rad = M_PI / 180;
        //Calculate distance from latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin($latitudeFrom * $rad)
            * sin($latitudeTo * $rad) + cos($latitudeFrom * $rad)
            * cos($latitudeTo * $rad) * cos($theta * $rad);

        $ret = acos($dist) / $rad * 60 * 1.853;
        return $ret;
    }

}
