<?php

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

require_once( './african-country-codes.php' );

$fileName = 'COVID-19 Outbreak Africa Map.svg';
// Load file into array
$contents = file( $fileName );

// Each array will contain the country codes for countries at that level.
$countries = array(
'level6' => [],
'level5' => [],
'level4' => [],
'level3' => [],
'level2' => [],
'level1' => []
);

// Parse CSV data file into associative array
function combine_array( &$row, $key, $header ) {
  $row = array_combine( $header, $row );
}
$rows = array_map( 'str_getcsv', file( '../data.csv' ) );
$header = array_shift( $rows );
array_walk( $rows, 'combine_array', $header );

// Assign a country/territory selector to a confirmed infection level
function assign_level( $selector, $confirmed ) {
	global $countries;
	switch (true) {
		case ( $confirmed >= 100000 ):
			$countries['level6'][] = $selector;
			break;
		case ( $confirmed >= 10000 ):
			$countries['level5'][] = $selector;
			break;
		case ( $confirmed >= 1000 ):
			$countries['level4'][] = $selector;
			break;
		case ( $confirmed >= 100 ):
			$countries['level3'][] = $selector;
			break;
		case ( $confirmed >= 10 ):
			$countries['level2'][] = $selector;
			break;
		case ( $confirmed >= 1 ):
			$countries['level1'][] = $selector;
			break;
	}
}

// Go through list of all African countries and assign to various infection levels
foreach( $rows as $row ) {
	// Find country rows
	if ( $row['FIPS'] === '' && $row['Admin2'] === '' && $row['Province_State'] === '' ) {
		$countryName = $row['Country_Region'];
		$confirmed = $row['Confirmed'];
		if ( array_key_exists( $countryName, $countrycodes ) ) {
			$selector = '.' . strtolower( $countrycodes[$countryName] );
			assign_level( $selector, $confirmed );
		}
	}
}

// Assign level for Mayotte territory
foreach( $rows as $row ) {
	// Find territory rows
	if ( $row['FIPS'] === '' && $row['Admin2'] === '' && $row['Province_State'] === 'Mayotte' ) {
		$confirmed = $row['Confirmed'];
		$selector = '.yt';
		assign_level( $selector, $confirmed );
	}
}

// Assign level for Reunion territory
foreach( $rows as $row ) {
	// Find territory rows
	if ( $row['FIPS'] === '' && $row['Admin2'] === '' && $row['Province_State'] === 'Reunion' ) {
		$confirmed = $row['Confirmed'];
		$selector = '.re';
		assign_level( $selector, $confirmed );
	}
}

// Map levels to CSS rules in the SVG file
$levelLines = array(
'level6' => 73,
'level5' => 79,
'level4' => 85,
'level3' => 91,
'level2' => 97,
'level1' => 103
);

// Add selectors for appropriate countries and territories to CSS rules
foreach( $levelLines as $level => $line ) {
	$selectors = implode( ', ', $countries[$level] ) . "\n";
	$contents[$line-1] = $selectors;
}

// Implode and save
file_put_contents( $fileName, implode( '', $contents ) );
