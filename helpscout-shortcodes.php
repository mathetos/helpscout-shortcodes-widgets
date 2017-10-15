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

function get_ratings_with_comments( $atts ) {

	$days = ( !empty($atts['days']) ? $atts['days'] : '30' );

	$atts = array(
		'days'  => $days,
	);

	$d = new DateTime( date('Y-m-d') );
	$d->modify( '- ' . $atts['days'] . ' days' );

	$start_date = $d->format( 'Y-m-d' );

	$ratings_args = array(
		'method'            => 'GET',
		'headers'           => array(
			'Authorization' => 'Basic ' . base64_encode( HSWPSC_HS_API_KEY  . ':' . 'X' )
		),
		'body'              => array(
			'page'   => 5,
			'rating' => 1,
			'start'  => $start_date . 'T00:00:00Z',
			'end'    => date( 'Y-m-d' ) . 'T23:59:59Z'
		),
	);

	$ratings_response = wp_remote_request( 'https://api.helpscout.net/v1/reports/happiness/ratings.json',  $ratings_args );

	if ( $ratings_response['response']['code'] == '401' ) {
		return false; // Bail early
	}

	$results = wp_remote_retrieve_body( $ratings_response );

	$data = json_decode( $results );

	foreach ($data->results as $rating ) {
		$comments = $rating->ratingComments;

		if ( !empty($comments) ) { ?>
			<p>"<?php echo $comments; ?>"
				<br /><small>Customer: <?php echo $rating->ratingCustomerName; ?></small>
				<br /><small>Date: <?php echo $rating->ratingCreatedAt; ?></small>
			</p>
		<?php }

	}
	//var_dump( $data->results );

}