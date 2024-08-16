<?php
require_once 'loranController.php';

// ==================================
// Sample use of the LORAN Controller
// ==================================

// =======================================================================================================
// IMPORTANT NOTE: The LORAN Controller does not include input filtering/sanitzation, rate limiting, etc., 
// all of which would be important in a production environment.
// =======================================================================================================

// =====================================================================
// These are the output formats that can be used as values for llFormat:
// ---------------------------------------------------------------------
// DMS (standard format: Degrees Minutes Seconds): 36 40 44 N, 73 49 08 W
// DMm (aka DDM: Degrees DecimalMinutes): 36 40.733 N, 73 49.133 W
// dec (aka DD: DecimalDegrees): 36.67889 N, 73.81889 W
// =====================================================================

// FYI: The first 3 variable names hint at the fact that LORAN-C makes use of time delays and repetition patterns of radio signals
$td1 = '26600'; // the first loran coordinate (TD = Time Delay)
$td2 = '41400'; // the second loran coordinate
$gri = '9960'; // the LORAN chain number (GRI = Group Repetition Interval, but it's also used as the chain number)
$llFormat = 'dec'; // 'DMS', 'dec', 'DMm'

$loranController = new LoranController($td1, $td2, $gri, $llFormat);
$result = $loranController->convert(); // returns result as JSON string
echo $result;