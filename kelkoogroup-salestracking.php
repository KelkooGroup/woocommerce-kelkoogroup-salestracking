<?php
/**
 * Plugin Name:       Kelkoogroup Sales Tracking
 * Description:       Plugin to contain kelkoogroup sales tracking customisation snippets.
 * Plugin URI:        https://github.com/KelkooGroup
 * Version:           1.0.0
 * Author:            Kelkoo
 * Author URI:        https://www.kelkoogroup.com/
 * Requires at least: 3.0.0
 * Tested up to:      4.4.2
 *
 * @package Kelkoogroup_SalesTracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Kelkoogroup_SalesTracking Class
 *
 * @class Kelkoogroup_SalesTracking
 * @version	1.0.0
 * @since 1.0.0
 * @package	Kelkoogroup_SalesTracking
 */
final class Kelkoogroup_SalesTracking {

	/**
	 * Set up the plugin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'kelkoogroup_salestracking_setup' ), -1 );
		require_once( 'inc/functions.php' );
	}

	/**
	 * Setup all the things
	 */
	public function kelkoogroup_salestracking_setup() {
		add_filter( 'wc_get_template',    array( $this, 'kelkoogroup_salestracking_wc_get_template' ), 11, 5 );
	}

	/**
	 * Override WooCommerce template
	 *
	 */
	public function kelkoogroup_salestracking_wc_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		$plugin_template_path = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/inc/templates/woocommerce/' . $template_name;

		if ( file_exists( $plugin_template_path ) ) {
			$located = $plugin_template_path;
		}

		return $located;
	}
} // End Class

/**
 * The 'main' function
 *
 * @return void
 */
function kelkoogroup_salestracking_main() {
	new Kelkoogroup_SalesTracking();
}

/**
 * Initialise the plugin
 */
add_action( 'plugins_loaded', 'kelkoogroup_salestracking_main' );

/**
 * Plugin admin
 */
add_action( 'admin_menu', 'kelkoogroup_salestracking_add_admin_menu' );
add_action( 'admin_init', 'kelkoogroup_salestracking_settings_init' );
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'kelkoogroup_action_links' );


function kelkoogroup_action_links( $links ) {
    $links = array_merge( array(
            '<a href="' . esc_url( admin_url( '/options-general.php?page=kelkoogroup-settings' ) ) . '">' . __( 'Settings', 'textdomain' ) . '</a>'  ), $links );
    return $links;
}

function kelkoogroup_salestracking_add_admin_menu(  ) {
    add_options_page( 'Kelkoogroup salestracking Page', 'Kelkoogroup', 'manage_options', 'kelkoogroup-settings', 'kelkoogroup_salestracking_options_page' );
}

function kelkoogroup_salestracking_settings_init(  ) {
    register_setting( 'kkSalesTrackingPlugin', 'kelkoogroup_salestracking_settings' );

    add_settings_section(
        'kelkoogroup_salestracking_kkSalesTrackingPlugin_section',
        __( 'Kelkoogroup Sales tracking', 'wordpress' ),
        'kelkoogroup_salestracking_settings_section_callback',
        'kkSalesTrackingPlugin'
    );

    add_settings_field(
        'kelkoogroup_salestracking_country',
        __( 'Country', 'wordpress' ),
        'kelkoogroup_salestracking_country_render',
        'kkSalesTrackingPlugin',
        'kelkoogroup_salestracking_kkSalesTrackingPlugin_section'
    );

    add_settings_field(
        'kelkoogroup_salestracking_comid',
        __( 'ComId', 'wordpress' ),
        'kelkoogroup_salestracking_comid_render',
        'kkSalesTrackingPlugin',
        'kelkoogroup_salestracking_kkSalesTrackingPlugin_section'
    );

}

function kelkoogroup_salestracking_country_render(  ) {
    $options = get_option( 'kelkoogroup_salestracking_settings' );
    ?>
    <input type='text' name='kelkoogroup_salestracking_settings[kelkoogroup_salestracking_country]' value='<?php echo $options['kelkoogroup_salestracking_country']; ?>'>
    <?php
}

function kelkoogroup_salestracking_comid_render(  ) {
    $options = get_option( 'kelkoogroup_salestracking_settings' );
    ?>
    <input type='text' name='kelkoogroup_salestracking_settings[kelkoogroup_salestracking_comid]' value='<?php echo $options['kelkoogroup_salestracking_comid']; ?>'>
    <?php
}

function kelkoogroup_salestracking_settings_section_callback(  ) {
    echo __( "<p>Kelkoogroup Sales Tracking requires a few details of the order.</p>
 <p>          ComId: This is the unique ID representing your shop within the Kelkoo system. </p>
 <p>          Country  is the 2-letter country code for the country on which your products are listed on Kelkoo:
 'at' for Austria, 'be' for Belgium, 'br' for Brazil, 'ch' for Switzerland, 'cz' for Czech Republic, 'de' for Germany,
 'dk' for Denmark, 'es' for Spain, 'fi' for Finland, 'fr' for France, 'ie ' for Ireland, 'it' for Italy, 'mx' for Mexico,
  'nb' for Flemish Belgium 'nl' for Netherlands, 'no' for Norway, 'pl' for Poland, 'pt' for Portugal, 'ru' for Russia,
  'se' for Sweden, 'uk' for United Kingdom, 'us' for United States... </p>
<p>You can get more information on <a href='https://www.kelkoogroup.com/kelkoo-customer-service/support-for-merchants/sales-tracking-guides/implement-kelkoo-
sales-tracking/'>https://www.kelkoogroup.com/kelkoo-customer-service/support-for-merchants/sales-tracking-guides/implement-kelkoo-sales-tracking/</a> </p>",
'wordpress' );
}

function kelkoogroup_salestracking_options_page(  ) {
    ?>
    <form action='options.php' method='post'>

        <h2>Kelkoogroup setting Page</h2>

        <?php
        settings_fields( 'kkSalesTrackingPlugin' );
        do_settings_sections( 'kkSalesTrackingPlugin' );
        submit_button();
        ?>

    </form>
    <?php
}