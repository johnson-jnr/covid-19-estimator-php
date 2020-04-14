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


