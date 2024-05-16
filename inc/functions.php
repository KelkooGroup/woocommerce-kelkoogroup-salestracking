<?php
/**
 * Functions.php
 *
 * @package  Kelkoogroup_SalesTracking
 * @author   Kelkoo Group
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Add lead tag
 */
function kelkoogroup_salestracking_call_leadtag_js() {
    echo '<script async="true" type="text/javascript" src="https://s.kk-resources.com/leadtag.js" ></script>';
}

/**
 * Store identifiers from URL parameters
 */
function kelkoogroup_salestracking_store_identifiers_from_url() {
    // Define an array of parameters and their associated keys
    $parameters = array(
        'kk' => 'kelkoogroup_salestracking_kk_identifier',
        'gclid' => 'kelkoogroup_salestracking_gclid_identifier',
        'kgclid' => 'kelkoogroup_salestracking_gclid_identifier',
        'msclkid' => 'kelkoogroup_salestracking_msclkid_identifier'
    );

    // Iterate over the array of parameters
    foreach ($parameters as $param_key => $transient_key) {
        // Check if the parameter is present in the URL
        if (isset($_GET[$param_key])) {
            // Store the value of the parameter in a transient with a lifespan of 365 days
            set_transient($transient_key, $_GET[$param_key], 365 * DAY_IN_SECONDS);
        }
    }
}

add_action( 'wp_head', 'kelkoogroup_salestracking_call_leadtag_js' );
add_action( 'init', 'kelkoogroup_salestracking_store_identifiers_from_url');
