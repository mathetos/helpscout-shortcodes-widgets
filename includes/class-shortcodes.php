<?php
/**
* Blocks_and_Shortcodes_4_HelpScout
*
 * @package   Blocks_and_Shortcodes_4_HelpScout
 * @author    Matt Cromwell <reachme@mattcromwell.com>
 * @copyright 2019
 * @license   GPLv3 or later
 * @link      https://www.mattcromwell.com
*/

/**
 * This class contain all the snippet or extra that improve the experience on the frontend
 */
class BAS4HS_Shortcode {
    /**
     * Initialize shortcodes
     */
    public function __construct() {
        add_shortcode( 'hs_great_ratings', array( $this, 'display_ratings_output' ) );

        wp_register_style( 'bas4hs-frontend', BAS4HS_URL . 'assets/styles/bas4hs-frontend-styles.css', array(), BAS4HS_VERSION, 'all' );
    }

    public static function display_ratings_output($atts) {

        wp_enqueue_style('bas4hs-frontend');

        $days = (!empty($atts['days']) ? $atts['days'] : '30');
        $ratings = (!empty($atts['rating_number']) ? $atts['rating_number'] : '1');

        $atts = array(
            'days' => $days,
            'rating_number' => $ratings,
        );

        $d = new DateTime(date('Y-m-d'));
        $d->modify('-' . $atts['days'] . ' days');

        $start_date = $d->format('Y-m-d');

        $ratings_args = array(
            'method' => 'GET',
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode(BAS4HS_HS_API_KEY . ':' . 'X')
            ),
            'body' => array(
                //'page'   => 5,
                'rating' => $atts['rating_number'],
                'start' => $start_date . 'T00:00:00Z',
                'end' => date('Y-m-d') . 'T23:59:59Z',
                'sortOrder' => 'DESC',
            ),
        );

        $ratings_response = wp_remote_request('https://api.helpscout.net/v1/reports/happiness/ratings.json', $ratings_args);

        if ($ratings_response['response']['code'] == '401') {
            return false; // Bail early
        }

        $results = wp_remote_retrieve_body($ratings_response);

        $data = json_decode($results);

        print_r($start_date . 'T00:00:00Z'); ?>

        <div class="bas4hs-reviews-wrapper">
            <?php

            foreach ($data->results as $rating) {
                $comments = $rating->ratingComments;

                if (!empty($comments)) { ?>
                    <div itemscope itemtype="http://schema.org/Service" class="bas4hs-item">
                        <div itemprop="description" class="bas4hs-description">
                            <p>"<?php echo $comments; ?>"</p>
                            <div class="bas4hs-meta">
                                <small>Support Rep: <?php echo $rating->ratingUserName; ?></small>
                                <br/>
                                <small>Customer: <?php echo $rating->ratingCustomerName; ?></small>
                                <br/>
                                <small>Date: <?php echo $rating->ratingCreatedAt; ?></small>
                            </div>
                        </div>
                    </div>
                <?php }

            }
        //var_dump($data->count);
        ?>
        <p>Total Great Ratings in the past <?php echo $atts['days']; ?> days: <?php echo $data->count; ?></p>
        </div>
        <?php
        //var_dump( $data->results );
    }
}

new BAS4HS_Shortcode();