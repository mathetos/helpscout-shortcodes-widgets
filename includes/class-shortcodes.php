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
        add_shortcode( 'hs_ratings', array( $this, 'display_ratings_output' ) );

        wp_register_style( 'bas4hs-frontend', BAS4HS_URL . 'assets/styles/bas4hs-frontend-styles.css', array(), mt_rand(10, 9999), 'all' );
    }

    public static function display_ratings_output($atts) {

        wp_enqueue_style('bas4hs-frontend');

        // Sett attribute defaults
        $days = (!empty($atts['days']) ? $atts['days'] : '30');
        $ratings = (!empty($atts['rating_number']) ? $atts['rating_number'] : '0');

        // Shortcode attributes
        $atts = array(
            'days' => $days,
            'rating_number' => $ratings,
        );

        // Format the Start Date
        $d = new DateTime(date('Y-m-d'));
        $d->modify('-' . $atts['days'] . ' days');
        $start_date = $d->format('Y-m-d');

        // Args for the GET Request
        $ratings_args = array(
            'timeout' => 45,
            'user-agent' => get_bloginfo( 'name' ),
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode(BAS4HS_HS_API_KEY . ':' . 'X')
            ),
            'body' => array(
                'page' => 1,
                'rating' => $atts['rating_number'],
                'start' => $start_date . 'T00:00:00Z',
                'end' => date('Y-m-d') . 'T23:59:59Z',
                'sortOrder' => 'DESC',
            ),
        );

        // Get the Ratings
        $ratings_response = wp_remote_get('https://api.helpscout.net/v1/reports/happiness/ratings.json', $ratings_args);

        // Bail if no ratings
        if ($ratings_response['response']['code'] == '401') {
            return false; // Bail early
        }

        // Get the BODY of the Ratings response
        $results = wp_remote_retrieve_body($ratings_response);
        $getdata = json_decode($results);

        $gettransient = get_transient('bas4hs-gallery-' . $getdata->results[0]->id);

        $data = (!empty($gettransient) ? $gettransient : $getdata);

        set_transient('bas4hs-gallery-' . $data->results[0]->id, $data, DAY_IN_SECONDS);

        // Buffer the output so the shortcode displays in the proper place on the page
        ob_start();

        ?>

        <div class="bas4hs-collection bas4hs-collection--gallery">

            <?php

            foreach ($data->results as $rating) {
                $comments = $rating->ratingComments;
                $getratingID = $rating->ratingId;
                $customer = $rating->ratingCustomerName;
                $support_rep = $rating->ratingUserName;
                $rating_date = $rating->ratingCreatedAt;
                $pretty_date = date("F j, Y", strtotime($rating_date));

                $ratingID = (
                ($getratingID == 1) ? __("GREAT Rating", 'bas4hs') :
                    (($getratingID == 2) ? __("OK Rating", 'bas4hs') :
                        (($getratingID == 3) ? __("Bad Rating", 'bas4hs') : '')));

                if (!empty($comments)) { ?>
                    <div itemscope itemtype="http://schema.org/Service" class="bas4hs-review--item rating-<?php echo $getratingID; ?>">
                        <header><span class="rating-id"><?php echo $ratingID; ?></span></header>
                        <div itemprop="description" class="bas4hs-description">
                            <p class="review-comment">"<?php echo $comments; ?>"</p>
                            <div class="bas4hs-meta">
                                <p><?php printf( esc_html__('This %1$s written by %2$s about Support Rep "%3$s" on %4$s' ), $ratingID, $customer, $support_rep, $pretty_date) ; ?></p>
                            </div>
                        </div>
                    </div>
                <?php }

            }
            ?>
            <p class="bas4hs-review--total">Total Great Ratings in the past <?php echo $atts['days']; ?>
                days: <?php echo $data->count; ?></p>
        </div>
        <?php
        $ob_str = ob_get_contents();
        ob_end_clean();

        return $ob_str;

        //var_dump( $data->results );
    }
}

new BAS4HS_Shortcode();