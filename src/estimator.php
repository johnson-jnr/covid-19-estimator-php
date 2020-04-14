<?php


function covid19ImpactEstimator($data) {


	$output = array();
	$output['data'] = $data;

	$timeToElapse				= $data['timeToElapse'];
	$periodType 				= $data['periodType'];
	$timeToElapse 				= getDaysToElapse( $timeToElapse, $periodType );

	//Challenge 1
	$currentlyInfected 				= $data['reportedCases'] * 10;
	$currentlyInfected_severely 			= $data['reportedCases'] * 50;
	$setOfDays 					= getSetNumberOfDays( $timeToElapse );
	$infectionsByRequestedTime 			= $currentlyInfected * ( 2 ** $setOfDays );
	$infectionsByRequestedTime_severely 		= $currentlyInfected_severely * ( 2 ** $setOfDays ); 

	//Challenge 2
	$severeCasesByRequestedTime 		 	= 0.15 * $infectionsByRequestedTime;
	$severeCasesByRequestedTime_severely 		= 0.15 * $infectionsByRequestedTime_severely;
	$totalHospitalBeds 				= 0.35 * $data['totalHospitalBeds'];
	$hospitalBedsByRequestedTime 			= $totalHospitalBeds - $severeCasesByRequestedTime;
	$hospitalBedsByRequestedTime_severely 		= $totalHospitalBeds - $severeCasesByRequestedTime_severely;

	//Challenge 3
	$casesForICUByRequestedTime 			= 0.05 * $infectionsByRequestedTime;
	$casesForICUByRequestedTime_severely 		= 0.05 * $infectionsByRequestedTime_severely;
	$casesForVentilatorsByRequestedTime 		= 0.02 * $infectionsByRequestedTime;
	$casesForVentilatorsByRequestedTime_severely	= 0.02 * $infectionsByRequestedTime_severely;
	$avgDailyIncomeInUSD 		= $data['region']['avgDailyIncomeInUSD'];
	$avgDailyIncomePopulation 	= $data['region']['avgDailyIncomePopulation'];
	$dollarsInFlight 		= ($infectionsByRequestedTime * $avgDailyIncomeInUSD * $avgDailyIncomePopulation) / $timeToElapse;
	$dollarsInFlight_severely	= ($infectionsByRequestedTime_severely * $avgDailyIncomeInUSD * $avgDailyIncomePopulation) / $timeToElapse;


	$output['impact']['currentlyInfected'] 		= (int) $currentlyInfected;
	$output['severeImpact']['currentlyInfected'] 		= (int) $currentlyInfected_severely;
	$output['impact']['infectionsByRequestedTime'] 		= (int) $infectionsByRequestedTime;
	$output['severeImpact']['infectionsByRequestedTime'] 	= (int) $infectionsByRequestedTime_severely;
	$output['impact']['severeCasesByRequestedTime'] 	= (int) $severeCasesByRequestedTime;
	$output['severeImpact']['severeCasesByRequestedTime'] 	= (int) $severeCasesByRequestedTime_severely;
	$output['impact']['hospitalBedsByRequestedTime'] 	= (int) $hospitalBedsByRequestedTime;
	$output['severeImpact']['hospitalBedsByRequestedTime']		= (int) $hospitalBedsByRequestedTime_severely;

	$output['impact']['casesForICUByRequestedTime']		= (int) $casesForICUByRequestedTime;
	$output['severeImpact']['casesForICUByRequestedTime'] 	= (int) $casesForICUByRequestedTime_severely;
	$output['impact']['casesForVentilatorsByRequestedTime']	= (int) $casesForVentilatorsByRequestedTime;
	$output['severeImpact']['casesForVentilatorsByRequestedTime']	= (int) $casesForVentilatorsByRequestedTime_severely;
	$output['impact']['dollarsInFlight']				= (int) $dollarsInFlight;
	$output['severeImpact']['dollarsInFlight']			= (int) $dollarsInFlight_severely;

	return $output;
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

$uri_path = array_filter(explode( '/', $uri ));
$content_type = end($uri_path);
$requestMethod = $_SERVER["REQUEST_METHOD"];


$time_pre = microtime(true);
if ( $content_type == 'xml' ) {

	header("Content-Type: application/xml; charset=UTF-8");
	$response['status_code_header'] = 'HTTP/1.1 200 OK';

	$input = trim(file_get_contents("PHP://input"));
	$input = json_decode($input, true);

		// dump($input);

	$result = covid19ImpactEstimator($input);

	$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><data/>');
	to_xml($xml, $result);
	$var = $xml->asXML();

	header($response['status_code_header']);
	echo $var;

		// $response['body'] = $var;
		// echo $response['body'];

} elseif ( $content_type == 'logs' ) {

	header("Content-Type: text/plain; charset=UTF-8");
	$response['status_code_header'] = 'HTTP/1.1 200 OK';

	$file = "logs.txt";
	$json = file_get_contents($file);

	echo $json;


} else {

	header("Content-Type: application/json; charset=UTF-8");
	$response['status_code_header'] = 'HTTP/1.1 200 OK';

	$input = trim(file_get_contents("PHP://input"));
	$input = json_decode($input, true);

		// dump($input);

	$result = covid19ImpactEstimator($input);

	header($response['status_code_header']);
	echo json_encode($result);


		// $response['body'] = json_encode($result);
		// echo $response['body'];

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

	$time_post = microtime(true);
	$exec_time = $time_post - $time_pre;
	$exec_time = strtok($exec_time, ".");
	$length = strlen($exec_time);

	if ($length < 2) {
		$exec_time = '0' . $exec_time;
	}

	$txt = "$requestMethod" . "\t\t" . "$uri" .  "\t\t" . $responseCode . "\t\t" . $exec_time . 'ms';
	$myfile = file_put_contents( 'logs.txt', $txt.PHP_EOL , FILE_APPEND );

}

