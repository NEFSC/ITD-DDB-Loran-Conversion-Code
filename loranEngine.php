<?php

/*
 * This class contains the algorithms derived from the BASIC program published by the U.S. Navy in the 1980s.
 * Since the math and the constraints of the BASIC language were quite difficult to re-engineer and re-factor, much of the 
 * arcane syntax, architecture, and variable names have been preserved.
 * 
 * Changing anything in this class is not recommended unless you are very familiar with the math and the BASIC program.
 */
class LoranEngine {
    private $A2 = 0;
    private $A = 0;
    private $A_arr = array();
    private $A1 = 0;
    private $AN = 0;
    private $A0 = -1;

    private $B2 = 0;
    private $B1 = 0;
    private $B = 0;
    private $B_arr = [];

    private $C0 = 1 / 298.26;
    private $C1 = 0;
    private $C2 = 0;
    private $C = 0;
    private $C_str;
    private $C_arr = [];

    private $D = 0;
    private $D_arr = [];
    private $D9_arr = [];
    private $D0 = 0;
    private $D1 = 0;
    private $D2 = 0;

    private $E1 = 0;
    private $E2 = 0;
    private $E = 0;

    private $F1 = 1;
    private $F2 = 1;
    private $F0 = 0;
    private $F = 0;

    private $G_arr = [];
    private $G = 0;

    private $H = 0;

    private $ITD;

    private $K = 0;

    private $L2 = 0;
    private $L1 = 0;
    private $L3 = 0;
    private $L4 = 0;
    private $L8 = 0;
    private $L9 = 0;
    private $L1_arr = [];
    private $L_arr = [];
    private $L0 = 0;
    private $L = 0;

    private $M = 0;
    private $M0 = 0;

    private $N1 = 0;
    private $N2 = 0;
    private $N3 = 0;
    private $N4 = 0;
    private $N5 = 0;
    private $N6 = 0;
    private $N7 = 0;
    private $N8 = 0;
    private $N9 = 15;
    private $N = 0;
    private $N0 = 0;

    private $PI;
    private $P2;
    private $P2_arr = [];
    private $P3 = 0;
    private $P4 = 0;
    private $P5 = 0;
    private $P6 = 0;
    private $P7 = 0;
    private $P8 = 0;
    private $P9 = 0;
    private $P = 0;
    private $P_str;
    private $P1;
    private $P1_arr = [];
    private $P_arr = [];
    private $P0 = 0;

    private $Q = 0;

    private $R = 0;
    private $RD;

    private $S0 = 0;
    private $S = 0;
    private $S1 = 0;
    private $S2 = 0;
    private $S8 = 0;
    private $S9 = 0;
    private $S3 = 0;
    private $slave = [];
    private $slave1;
    private $slave2;

    private $TP;
    private $T = 0;
    private $T_arr = [];
    private $T1 = 0;
    private $T2 = 0;
    private $traceList = '<table>';

    private $U = 0;

    private $V = 0;

    private $W = 0;

    private $X = 0;
    private $XX = 0;
    private $X_arr = [];
    private $X0;
    private $X_str;

    private $YY = 0;
    private $Y = 0;

    private $ZZ = 0;
    private $Z = 0;
    private $Z_arr = [];
    private $Z1 = 0;
    private $Z8 = 0;
    private $Z9 = 0;
    private $Z2 = 0;
    private $logged = false;
    private $towersObj = [];
    private $error = [];

    public function __Construct(array $chainArray) {
        $this->towersObj = $chainArray;
        $this->PI = 4 * atan(1);
        $this->RD = $this->PI / 180;
        $this->TP = $this->PI + $this->PI;
    }

    // return the sign of a number
    private function sign($num) {
        $sign = 0;
        if ($num !== 0) {
            $sign = $num > 0 ? 1 : -1;
        }
        return $sign;
    }

    private function ItdValidContinue($i) {
        $this->A_arr[$i] = $this->ITD / 21295.8736;
    }

    /* 
     * This is the main conversion method 
     *
     * @param int $GRI
     * @param int $loran1
     * @param int $loran2
     * @param int $a
     *
     * @return array
     */
    public function convertLoranToLL($GRI, $loran1, $loran2, $a) {
        $this->A0 = $a;
        $this->slave[0] = $this->slave1 = $this->findSlave($GRI, $loran1);
        /* if ($this->error != '') {
            die(json_encode(array("error" => $this->error)));
        } */
        $this->slave[1] = $this->slave2 = $this->findSlave($GRI, $loran2);
        /* if (count($this->error) > 0) {
            die(json_encode(array("error" => $this->error)));
        } */
        array_push($this->D_arr, $loran1, $loran2);

        // get coords and delays for all towers in this GRI triad
        $this->P1 = $this->towersObj[$GRI][$this->slave1]['lat'];
        $this->L1 = $this->towersObj[$GRI][$this->slave1]['long'];
        $this->P2 = $this->towersObj[$GRI][$this->slave2]['lat'];
        $this->L2 = $this->towersObj[$GRI][$this->slave2]['long'];
        array_push($this->P1_arr, $this->P1);
        array_push($this->L1_arr, $this->L1);
        array_push($this->D9_arr, $this->towersObj[$GRI][$this->slave1]['delay']);
        array_push($this->D9_arr, $this->towersObj[$GRI][$this->slave2]['delay']);
        array_push($this->P1_arr, $this->P2);
        array_push($this->L1_arr, $this->L2);

        $this->P0 = $this->towersObj[$GRI]['lat'];
        $this->L0 = $this->towersObj[$GRI]['long'];

        for ($i = 0; $i < 2; $i++) {
            $this->G_arr[$i] = $GRI . $this->slave[$i];
            $this->C = $this->towersObj[$GRI][$this->slave[$i]]['delay'];
            $this->X = $this->P0;
            $this->useAcosQatn();
            $this->P_arr[0][$i] = $this->X;
            $this->X = $this->L0;
            $this->acos();
            $this->L_arr[0][$i] = $this->V * $this->RD;
            $this->X = $this->P1_arr[$i];
            $this->useAcosQatn();
            $this->P_arr[1][$i] = $this->X;
            $this->X = $this->L1_arr[$i];
            $this->acos();

            $this->L_arr[1][$i] = $this->V * $this->RD;
        }

        $this->setF1AndF2();

        for ($i = 0; $i < 2; $i++) {
            $this->ITD = $this->D_arr[$i];
            $this->ITD = $this->ITD + $this->G_arr[$i] - $this->D9_arr[$i] - $this->T_arr[$i];

            if (abs($this->ITD) < $this->T_arr[$i]) {
                $this->ItdValidContinue($i);
            }
            else {
                $this->error = 'ERROR: ITD ' . $this->D_arr[$i] . ' NOT VALID FOR GRI ' . $GRI;
                die(json_encode(array("error" => $this->error)));
            }
        }
        $this->fixingRoutine();
        $ret = [];
        $ret['GRI'] = $GRI;
        $ret['slaves'] = $this->slave;
        $ret['targetLocation']['lat'] = $this->C_arr[0];
        $ret['targetLocation']['lon'] = $this->C_arr[1];

        //$fishingArea = $this->getFishingArea();

        //$ret['targetLocation']['fishingArea'] = $fishingArea;

        return $ret;
    }

    private function acos() {
        $this->S = $this->sign($this->X);
        $this->X = abs($this->X);
        $this->H = intval($this->X);
        $this->M0 = 1;
        $this->modFnSetup();
        $this->V = $this->X * 100;
        $this->X = $this->V;
        $this->modFnSetup();
        $this->V = $this->S * ((100 * $this->X / 60 + intval($this->V)) / 60 + $this->H);
    }

    private function setF1AndF2() {
        $this->F1 = 1;
        $this->F2 = 1;
        $error = false;

        if ($this->P_arr[0][0] == $this->P_arr[0][1] && $this->L_arr[0][0] == $this->L_arr[0][1]) {
            $this->doWork();
        }
        else if ($this->P_arr[0][0] == $this->P_arr[1][1] && $this->L_arr[0][0] == $this->L_arr[1][1]) {
            $this->F2 = -1;
            $this->doWork();
        }
        else if ($this->P_arr[1][0] == $this->P_arr[1][1] && $this->L_arr[1][0] == $this->L_arr[1][1]) {
            $this->F1 = -1;
            $this->doWork();
        }
        else if ($this->P_arr[1][0] == $this->P_arr[0][1] && $this->L_arr[1][0] == $this->L_arr[0][1]) {
            $this->F2 = 2;
            $this->doWork();
        }
        else {
            $this->error = "ERROR: No Triplet Possible";
            die(json_encode(array("error" => $this->error)));
        }
    }

    private function modFnSetup() {
        $this->X = $this->X - $this->M0 * intval($this->X / $this->M0);
        $this->modFn();
    }

    private function modFn() {
        $xxBoola = $this->XX == 0 ? -1 : 0;
        $xxBoolb = $this->XX < 0 ? -1 : 0;
        $this->AN = atan($this->YY / ($this->XX - 1e-9 * $xxBoola)) - $this->PI * $xxBoolb;
    }

    private function qatn() {
        $this->X = atan((1 - $this->C0) * tan($this->X));
    }

    private function setUpVars($i) {
        $this->reverseSolution();
        $this->B_arr[$i] = $this->S0;
        $this->Z_arr[0][$i] = $this->Z1;
        $this->Z_arr[1][$i] = $this->Z2;
        $this->math4($i);
    }

    // secondary phase correct if time delay >= 537 micro secs
    private function math2() {
        $this->P = 129.04398 / $this->T - 0.40758 + 0.645765438e-3 * $this->T;
    }

    // secondary phase correct if time delay < 537 micro secs
    private function math3() {
        $this->P = 2.7412979 / $this->T - 0.32774624 - 3 * $this->T;
    }

    private function math4($i) {
        $this->T = 21282.3593 * $this->S0;
        // secondary phase correction
        if ($this->T >= 537) {
            $this->math2();
        }
        else {
            $this->math3();
        }
        $this->T_arr[$i] = $this->T + $this->P;
    }

    private function doWork() {
        for ($i = 0; $i < 2; $i++) {
            $this->P1 = $this->P_arr[0][$i];
            $this->L1 = $this->L_arr[0][$i];
            $this->P2 = $this->P_arr[1][$i];
            $this->L2 = $this->L_arr[1][$i];
            $this->setUpVars($i);
        }

        $this->G_arr[0] = 0;
        $this->G_arr[1] = 0;
    }

    private function useAcosQatn() {
        $this->acos();
        $this->X = $this->V * $this->RD;
        $this->qatn();
    }

    private function asin() {
        $boola = $this->XX == 0 ? -1 : 0;
        $boolb = $this->XX < 0 ? -1 : 0;
        $this->AN = atan(sqrt(1 - $this->XX * $this->XX) / ($this->XX - 1e-9 * $boola)) - $this->PI * $boolb;
    }

    // reverse solution - was basic subroutine 650
    private function reverseSolution() {
        $this->L3 = $this->L2 - $this->L1;
        $this->P3 = ($this->P2 - $this->P1) / 2;
        $this->P4 = ($this->P1 + $this->P2) / 2;
        $this->P6 = sin($this->P3);
        $this->P7 = cos($this->P3);
        $this->P8 = sin($this->P4);
        $this->P9 = cos($this->P4);
        $this->H = $this->P7 * $this->P7 - $this->P8 * $this->P8;
        $this->L = $this->P6 * $this->P6 + $this->H * sin($this->L3 / 2) ** 2;
        $this->XX = sqrt($this->L);
        $this->setZzAndAn();
        $this->D0 = 2 * $this->AN;
        $this->U = 2 * $this->P8 * $this->P8 * $this->P7 * $this->P7 / (1 - $this->L);
        $this->V = 2 * $this->P6 * $this->P6 * $this->P9 * $this->P9 / $this->L;
        $this->X = $this->U + $this->V;
        $this->Y = $this->U - $this->V;
        $this->T = $this->D0 / sin($this->D0);

        $this->D = 4 * $this->T * $this->T;
        $this->E = 2 * cos($this->D0);

        $this->A = $this->D * $this->E;

        $this->C = $this->T - ($this->A - $this->E) / 2;
        $this->N1 = $this->X * ($this->A + $this->C * $this->X);
        $this->B = $this->D + $this->D;
        $this->N2 = $this->Y * ($this->B + $this->E * $this->Y);
        $this->N3 = $this->D * $this->X * $this->Y;

        $this->D2 = $this->C0 * $this->C0 * ($this->N1 - $this->N2 + $this->N3) / 64;
        $this->D1 = $this->C0 * ($this->T * $this->X - $this->Y) / 4;
        $this->S0 = ($this->T - $this->D1 + $this->D2) * sin($this->D0);
        $this->M = 32 * $this->T - (20 * $this->T - $this->A) * $this->X - ($this->B + 4) * $this->Y;
        $this->F = $this->Y + $this->Y - $this->E * (4 - $this->X);
        $this->G = $this->C0 * ($this->T / 2 + $this->C0 * $this->M / 64);
        $this->Q = -$this->F * $this->G * tan($this->L3) / 4;

        $this->L4 = ($this->L3 + $this->Q) / 2;
        $this->L8 = sin($this->L4);
        $this->L9 = cos($this->L4);

        $this->YY = $this->P6 * $this->L9;
        $this->XX = $this->P9 * $this->L8;
        $this->modFn();
        $this->T1 = $this->AN;
        $this->YY = -$this->P7 * $this->L9;
        $this->XX = $this->P8 * $this->L8;

        $this->modFn();
        $this->T2 = $this->AN;
        $this->M0 = $this->TP;
        $this->X = $this->T1 + $this->T2;

        $this->modFnSetup();
        $this->Z1 = $this->X;
        $this->X = $this->T1 - $this->T2;
        $this->modFnSetup();
        $this->Z2 = $this->X;
    }

    // Direct Solution - was basic subroutine 550
    private function directSolution() {

        $this->Z8 = sin($this->Z1);
        $this->Z9 = cos($this->Z1);
        $this->P8 = sin($this->P1);
        $this->P9 = cos($this->P1);
        $this->M = -$this->Z8 * $this->P9;
        $this->C1 = $this->C0 * $this->M;
        $this->C2 = $this->C0 * (1 - $this->M * $this->M) / 4;
        $this->D = (1 - $this->C2) * (1 - $this->C2 - $this->C1 * $this->M);
        $this->P = $this->C2 * (1 + $this->C1 * $this->M / 2) / $this->D;
        $this->N = $this->P9 * $this->Z9;
        $this->YY = $this->N;
        $this->XX = $this->P8;

        $this->modFn();
        $this->S1 = $this->AN;
        $this->D0 = $this->S0 / $this->D;
        $this->U = 2 * ($this->S1 - $this->D0);
        $this->W = 1 - 2 * $this->P * cos($this->U);
        $this->V = cos($this->U + $this->D0);
        $this->X = $this->C2 * $this->C2 * sin($this->D0) * cos($this->D0 * (2 * $this->V * $this->V - 1));
        $this->Y = 2 * $this->P * $this->V * $this->W * sin($this->D0);
        $this->S2 = $this->D0 + $this->X - $this->Y;
        $this->S8 = sin($this->S2);
        $this->S9 = cos($this->S2);
        $this->K = sqrt($this->M * $this->M + ($this->N * $this->S9 - $this->P8 * $this->S8) ** 2);

        $this->P2 = atan(($this->P8 * $this->S9 + $this->N * $this->S8) / $this->K);
        $this->YY = -$this->S8 * $this->Z8;
        $this->XX = $this->P9 * $this->S9 - $this->P8 * $this->S8 * $this->Z9;

        $this->modFn();
        $this->S3 = $this->AN;
        $this->H = $this->C1 * (1 - $this->C2) * $this->S2 - $this->C1 * $this->C2 * $this->S8 * cos($this->S1 + $this->S1 - $this->S2);
        $this->L2 = $this->L1 + $this->S3 - $this->H;
        $this->YY = -$this->M;
        $this->XX = -($this->N * $this->S9 - $this->P8 * $this->S8);

        $this->modFn();
        $this->Z2 = $this->AN;
    }

    private function setZzAndAn() {
        $this->ZZ = sqrt(1 - $this->XX * $this->XX);
        $boola = $this->ZZ == 0 ? -1 : 0;
        $this->AN = atan($this->XX / ($this->ZZ - 1e-9 * $boola));
    }

    //FIXING ROUTINE - was basic subroutine 770
    private function fixingRoutine() {
        $this->A1 = $this->F1 * sin($this->A_arr[0]);
        $this->B1 = cos($this->A_arr[0]) - cos($this->B_arr[0]);
        $this->C1 = sin($this->B_arr[0]);
        $this->A2 = $this->F2 * sin($this->A_arr[1]);
        $this->B2 = cos($this->A_arr[1]) - cos($this->B_arr[1]);
        $this->C2 = sin($this->B_arr[1]);
        $this->E1 = $this->Z_arr[0][0];

        if ($this->F1 == -1) {
            $this->E1 = $this->Z_arr[1][0];
        }

        $this->E2 = $this->Z_arr[0][1];

        if ($this->F2 == -1) {
            $this->E2 = $this->Z_arr[1][1];
        }

        $this->C = $this->B1 * $this->C2 * cos($this->E2) - $this->B2 * $this->C1 * cos($this->E1);
        $this->S = $this->B1 * $this->C2 * sin($this->E2) - $this->B2 * $this->C1 * sin($this->E1);
        $this->K = $this->B2 * $this->A1 - $this->B1 * $this->A2;
        $this->R = sqrt($this->C * $this->C + $this->S * $this->S);
        $this->YY = $this->S;
        $this->XX = $this->C;
        $this->modFn();

        $this->G = $this->AN;
        $this->XX = $this->K / $this->R;
        $this->asin();
        $this->Z = $this->G + $this->A0 * $this->AN;

        $this->YY = $this->B2;
        $this->XX = $this->C2 * cos($this->Z - $this->E2) + $this->A2;
        $this->modFn();
        $this->S0 = $this->AN;

        if ($this->F2 == 1) {
            $this->P1 = $this->P_arr[0][1];
            $this->L1 = $this->L_arr[0][1];
        }
        if ($this->F2 == -1) {
            $this->P1 = $this->P_arr[1][1];
            $this->L1 = $this->L_arr[1][1];
        }
        $this->Z1 = $this->Z;

        $this->directSolution();
        $this->P0 = $this->P2;
        $this->L0 = $this->L2;
        $this->P = atan(tan($this->P0) / (1 - $this->C0));
        $this->P = $this->P / $this->RD;
        $this->X = $this->L0 / $this->RD;
        $this->M0 = 360;
        $this->modFnSetup();
        $this->L = $this->X;

        $this->L = $this->L > 180 ? $this->L - 360 : $this->L;
        $this->X = $this->P;

        $this->buildString();
        $this->P_str = $this->C_str;
        $this->X = $this->L;
        $this->buildString();
    }

    /* builds string values for Lat and Lon that will be returned to the caller */
    private function buildString() {
        $this->C_str = "";

        if ($this->X < 0) {
            $this->C_str = "-";
            $this->X = -$this->X;
        }

        $this->X = $this->X + 1 / 7200;
        $this->X0 = intval($this->X);
        $this->C_str = $this->C_str . strval($this->X0) . " ";
        $this->X = 60 * ($this->X - $this->X0);
        $this->X0 = intval($this->X);
        $st = 100 . $this->X0;
        $this->X_str = strval($st);
        $this->C_str = $this->C_str . substr($this->X_str, -2);
        $this->X = 60 * ($this->X - $this->X0);
        $this->X0 = intval($this->X);
        $st2 = 100 . $this->X0;
        $this->X_str = strval($st2);
        $this->C_str = $this->C_str . " " . substr($this->X_str, -2);
        array_push($this->C_arr, $this->C_str);
    }

    /*
     * Find the slave tower for a given GRI and loran number
     *
     * @param int $GRI
     * @param int $loranNum
     *
     * @return string
     */
    private function findSlave($GRI, $loranNum) {
        $griArray = $this->towersObj[$GRI];
        $possibleSlave = '';
        $foundSlave = '';
        foreach ($griArray as $key => $val) {
            if (is_array($val)) {
                $slaveLetter = $key;
                $delay = $val['delay'];
                $possibleSlave = $loranNum >= $delay ? $slaveLetter : $possibleSlave;
            }
        }
        if ($possibleSlave != '') {
            $foundSlave = $possibleSlave;
        }
        else {
            $this->error = "ERROR: No slave found for GRI: $GRI and loranNum:  $loranNum";
            die(json_encode(array("error" => $this->error)));
        }
        return $foundSlave;
    }


    // get tenmsq 
    private function getTenMSq($latmin, $longmin) {
        if ($longmin >= 0 and $longmin < 10) {
            $getTenmsqCalc = "6";
        }
        elseif ($longmin >= 10 and $longmin < 20) {
            $getTenmsqCalc = "5";
        }
        elseif ($longmin >= 20 and $longmin < 30) {
            $getTenmsqCalc = "4";
        }
        elseif ($longmin >= 30 and $longmin < 40) {
            $getTenmsqCalc = "3";
        }
        elseif ($longmin >= 40 and $longmin < 50) {
            $getTenmsqCalc = "2";
        }
        elseif ($longmin >= 50 and $longmin < 60) {
            $getTenmsqCalc = "1";
        }

        if ($latmin >= 0 and $latmin < 10) {
            $getTenmsqCalc = $getTenmsqCalc . "6";
        }
        elseif ($latmin >= 10 and $latmin < 20) {
            $getTenmsqCalc = $getTenmsqCalc . "5";
        }
        elseif ($latmin >= 20 and $latmin < 30) {
            $getTenmsqCalc = $getTenmsqCalc . "4";
        }
        elseif ($latmin >= 30 and $latmin < 40) {
            $getTenmsqCalc = $getTenmsqCalc . "3";
        }
        elseif ($latmin >= 40 and $latmin < 50) {
            $getTenmsqCalc = $getTenmsqCalc . "2";
        }
        elseif ($latmin >= 50 and $latmin < 60) {
            $getTenmsqCalc = $getTenmsqCalc . "1";
        }

        return $getTenmsqCalc;
    }

}