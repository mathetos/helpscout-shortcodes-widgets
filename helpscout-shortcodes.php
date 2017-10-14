<?php
/*
Plugin Name: HelpScout Shortcodes and Widgets
Plugin URI: https://www.mattcromwell.com/
Description: Shortcodes to output HelpScout data into your WordPress website.
Version: 1.0
Author: Matt Cromwell
Author URI: https://www.mattcromwell.com
License: GPLv2 or later
Text Domain: hswpsc
*/

define( 'HSWPSC_SDK_DIR', __DIR__ . '\sdks' );
define( 'HSWPSC_HS_SDK', __DIR__ . '\sdks\helpscout' );


add_shortcode( 'hs_great_ratings', 'hs_great_ratings_function' );

include HSWPSC_HS_SDK . '\src\HelpScout\ApiClient.php';
include HSWPSC_HS_SDK . '\vendor\autoload.php';

use HelpScout\ApiClient;

function hs_great_ratings_function( $atts ) {

	$hs = ApiClient::getInstance();
	$hs->setKey( HSWPSC_HS_API_KEY );

	$ratings = $hs->getHappinessRatingsReport( [
		'page'   => 10,
		'rating' => 0,
		'start'  => '2017-01-01T00:00:00Z',
		'end'    => '2017-12-31T23:59:59Z',
		'start'  => date( 'Y-m-d', strtotime( '-120 days' ) ) . 'T00:00:00Z',
		'end'    => date( 'Y-m-d' ) . 'T23:59:59Z'
	] );
	$results = $ratings->results;

	//var_dump( $results );
	ob_start();
	foreach ( $results as $rating ) {
		$comment  = $rating->ratingComments;
		$customer = $rating->ratingCustomerName;

		if ( ! empty( $comment ) ) {
			echo '<p>"' . $comment . '" ~<em>' . $customer . '</em></p>';
		}
	}

	$output = ob_get_clean();

	return $output;
}
