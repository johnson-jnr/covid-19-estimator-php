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



	$output['impact']['currentlyInfected'] 		= (int) $currentlyInfected;
	$output['severeImpact']['currentlyInfected'] 		= (int) $currentlyInfected_severely;
	$output['impact']['infectionsByRequestedTime'] 		= (int) $infectionsByRequestedTime;
	$output['severeImpact']['infectionsByRequestedTime'] 	= (int) $infectionsByRequestedTime_severely;	

	return $data;
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