<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * functions.php
 * Add PHP snippets here
 */
function call_leadtag_js() {
    echo '<script async="true" type="text/javascript" src="https://s.kk-resources.com/leadtag.js" ></script>';
}

add_action( 'wp_head', 'call_leadtag_js' );
