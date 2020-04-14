<?php


function covid19ImpactEstimator($data) {


	$estimate = array();
	$estimate['data'] = $data;

	$timeToElapse				= $data['timeToElapse'];
	$periodType 				= $data['periodType'];
	$timeToElapse 			= getDaysToElapse( $timeToElapse, $periodType );

	$currentlyInfected 											= $data['reportedCases'] * 10;
	$currentlyInfected_severely 						= $data['reportedCases'] * 50;
	$setOfDays 															= getSetNumberOfDays( $timeToElapse );
	$infectionsByRequestedTime 							= $currentlyInfected * ( 2 ** $setOfDays );
	$infectionsByRequestedTime_severely 		= $currentlyInfected_severely * ( 2 ** $setOfDays ); 

	$severeCasesByRequestedTime 		 				= 0.15 * $infectionsByRequestedTime;
	$severeCasesByRequestedTime_severely 		= 0.15 * $infectionsByRequestedTime_severely;
	$totalHospitalBeds 											= 0.35 * $data['totalHospitalBeds'];
	$hospitalBedsByRequestedTime 						= $totalHospitalBeds - $severeCasesByRequestedTime;
	$hospitalBedsByRequestedTime_severely 	= $totalHospitalBeds - $severeCasesByRequestedTime_severely;

	$casesForICUByRequestedTime 									= 0.05 * $infectionsByRequestedTime;
	$casesForICUByRequestedTime_severely 					= 0.05 * $infectionsByRequestedTime_severely;
	$casesForVentilatorsByRequestedTime 					= 0.02 * $infectionsByRequestedTime;
	$casesForVentilatorsByRequestedTime_severely	= 0.02 * $infectionsByRequestedTime_severely;
	$avgDailyIncomeInUSD 													= $data['region']['avgDailyIncomeInUSD'];
	$avgDailyIncomePopulation 										= $data['region']['avgDailyIncomePopulation'];
	$dollarsInFlight 															= ($infectionsByRequestedTime * $avgDailyIncomeInUSD * $avgDailyIncomePopulation) / $timeToElapse;
	$dollarsInFlight_severely											= ($infectionsByRequestedTime_severely * $avgDailyIncomeInUSD * $avgDailyIncomePopulation) 
																										/ $timeToElapse;


	$estimate['impact']['currentlyInfected'] 												= (int) $currentlyInfected;
	$estimate['severeImpact']['currentlyInfected'] 									= (int) $currentlyInfected_severely;
	$estimate['impact']['infectionsByRequestedTime'] 								= (int) $infectionsByRequestedTime;
	$estimate['severeImpact']['infectionsByRequestedTime'] 					= (int) $infectionsByRequestedTime_severely;
	$estimate['impact']['severeCasesByRequestedTime'] 							= (int) $severeCasesByRequestedTime;
	$estimate['severeImpact']['severeCasesByRequestedTime'] 				= (int) $severeCasesByRequestedTime_severely;
	$estimate['impact']['hospitalBedsByRequestedTime'] 							= (int) $hospitalBedsByRequestedTime;
	$estimate['severeImpact']['hospitalBedsByRequestedTime']				= (int) $hospitalBedsByRequestedTime_severely;

	$estimate['impact']['casesForICUByRequestedTime']								= (int) $casesForICUByRequestedTime;
	$estimate['severeImpact']['casesForICUByRequestedTime'] 				= (int) $casesForICUByRequestedTime_severely;
	$estimate['impact']['casesForVentilatorsByRequestedTime']				= (int) $casesForVentilatorsByRequestedTime;
	$estimate['severeImpact']['casesForVentilatorsByRequestedTime']	= (int) $casesForVentilatorsByRequestedTime_severely;
	$estimate['impact']['dollarsInFlight']													= (int) $dollarsInFlight;
	$estimate['severeImpact']['dollarsInFlight']										= (int) $dollarsInFlight_severely;

	return $estimate;
}


function getSetNumberOfDays( $timeToElapse ) {

	$setOfDays = $timeToElapse / 3;
	$setOfDays = (int) $setOfDays;

	return $setOfDays;
}

function getDaysToElapse($timeToElapse, $periodType) {

	if ($periodType == 'weeks') {
		$timeToElapse = $timeToElapse * 7;
	} elseif ($periodType == 'months') {
		$timeToElapse = $timeToElapse * 30;
	} 

	return $timeToElapse;
}


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri_path 			= array_filter(explode( '/', $uri ));
$content_type 	= end($uri_path);
$requestMethod 	= $_SERVER["REQUEST_METHOD"];


// Endpoint start with /api else results in a 404 Not Found
if ($uri_path[1] !== 'api') {
    header("HTTP/1.1 404 Not Found");
    exit();
}


$time_pre_exec = microtime(true);
if ( $content_type == 'xml' ) {

	header("Content-Type: application/xml; charset=UTF-8");
	$input = trim(file_get_contents("PHP://input"));
	$input = json_decode($input, true);

	$estimate = covid19ImpactEstimator($input);
	$xml 			= new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><data/>');
	to_xml($xml, $estimate);
	$result = $xml->asXML();

	$response['status_code_header'] = 'HTTP/1.1 200 OK';
	$response['body'] =  $result;
	return $response
	
} elseif ( $content_type == 'logs' ) {

	header("Content-Type: text/plain; charset=UTF-8");
	$file = "logs.txt";
	$logs = file_get_contents($file);

	$response['status_code_header'] = 'HTTP/1.1 200 OK';
	$response['body'] =  $logs;
	return $response;

} else {

	header("Content-Type: application/json; charset=UTF-8");

	$input = trim(file_get_contents("PHP://input"));
	$input = json_decode($input, true);

	$estimate	= covid19ImpactEstimator($input);
	$response['status_code_header'] = 'HTTP/1.1 200 OK';
	$response['body'] = json_encode($estimate);
	return $response;
}

logRequest( $requestMethod, $uri, $time_pre );


function to_xml(SimpleXMLElement $object, array $data) {   

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $new_object = $object->addChild($key);
            to_xml($new_object, $value);
        } else {
            // if the key is an integer, it needs text with it to actually work.
            if ($key == (int) $key) {
                $key = "$key";
            }

            $object->addChild($key, $value);
        }   
    }   
} 


function logRequest( $requestMethod, $uri, $time_pre ) {

	$responseCode = http_response_code(); 

	$time_post_exec = microtime(true);
	$exec_time 			= $time_post_exec - $time_pre_exec;
	$exec_time 			= strtok($exec_time, ".");
	$length 				= strlen($exec_time);

	if ($length < 2) {
		$exec_time = '0' . $exec_time;
	}

	$log_txt = "$requestMethod" . "\t\t" . "$uri" .  "\t\t" . $responseCode . "\t\t" . $exec_time . 'ms';
	file_put_contents( 'logs.txt', $log_txt.PHP_EOL , FILE_APPEND );

}

