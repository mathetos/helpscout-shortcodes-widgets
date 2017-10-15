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

define( 'HSWPSC_HS_SDK', __DIR__ . '\vendor\HelpScout' );
define( 'HSWPSC_URL', plugin_dir_url( __FILE__ ) );
define( 'HS_SDK_PATH', plugin_dir_path( __FILE__ ) );

add_shortcode( 'hs_great_ratings', 'get_ratings_with_comments' );

function hs_great_ratings_function( $atts, $days ) {
	$number = ( !empty($atts['number']) ? $atts['number'] : '3' );
	$days = ( !empty($atts['days']) ? $atts['days'] : '14' );

	$atts = array(
		'number'    => $number,
		'days'      => $days,
	);

	$review = get_ratings_with_comments( $days = $atts['days'] );

	var_dump($review);

}

function get_ratings_with_comments($days = '') {

	// Arguments for POSTing the Invitation to Checkr based on the Candidate we just created.
	$today = date( 'Y-m-d' );
	$start_date = date('Y-m-d', strtotime($today.' - ' .$days));

	$ratings_args = array(
		'method'            => 'GET',
		'headers'           => array(
			'Authorization' => 'Basic ' . base64_encode( HSWPSC_HS_API_KEY  . ':' . 'X' )
		),
		'body'              => array(
			'page'   => 5,
			'rating' => 0,
			'start'  => $start_date . 'T00:00:00Z',
			'end'    => date( 'Y-m-d' ) . 'T23:59:59Z'
		),
	);

	$ratings_response = wp_remote_request( 'https://api.helpscout.net/v1/reports/happiness/ratings.json',  $ratings_args );
	
	if ( is_wp_error( $ratings_response) ) {
		return false; // Bail early
	}

	$results = wp_remote_retrieve_body( $ratings_response );

	$data = json_decode( $results );

	var_dump( $data );

}