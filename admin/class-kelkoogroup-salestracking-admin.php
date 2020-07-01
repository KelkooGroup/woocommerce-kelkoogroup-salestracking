<?php
 /**
 * The admin-specific functions of the plugin.
 *
 * @link        https://github.com/KelkooGroup/woocommerce-kelkoogroup-salestracking
 * @since       1.0.0
 * Author:      Kelkoo Group
 * Author URI:  https://www.kelkoogroup.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * @package     Kelkoogroup_SalesTracking
 * @subpackage  Kelkoogroup_SalesTracking/admin
 */

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
        __( 'Merchant Identifier', 'wordpress' ),
        'kelkoogroup_salestracking_comid_render',
        'kkSalesTrackingPlugin',
        'kelkoogroup_salestracking_kkSalesTrackingPlugin_section'
    );

     add_settings_field(
        'kelkoogroup_salestracking_multicomid',
        __( 'Multi Merchant Information', 'wordpress' ),
        'kelkoogroup_salestracking_multicomid_render',
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

function kelkoogroup_salestracking_multicomid_render(  ) {
    $options = get_option( 'kelkoogroup_salestracking_settings' );
    ?>
    <input type='text' name='kelkoogroup_salestracking_settings[kelkoogroup_salestracking_multicomid]' value='<?php echo $options['kelkoogroup_salestracking_multicomid']; ?>'>
    <?php
}


function kelkoogroup_salestracking_settings_section_callback(  ) {
    echo __( "<p>Kelkoogroup Sales Tracking requires a few details of the order.</p>
 <p>          Merchant Identifier: This is the unique ID representing your shop within the Kelkoo system. </p>
 <p>          Country is the 2-letter country code for the country on which your products are listed on Kelkoo:
 'at' for Austria, 'be' for Belgium, 'br' for Brazil, 'ch' for Switzerland, 'cz' for Czech Republic, 'de' for Germany,
 'dk' for Denmark, 'es' for Spain, 'fi' for Finland, 'fr' for France, 'ie ' for Ireland, 'it' for Italy, 'mx' for Mexico,
  'nb' for Flemish Belgium 'nl' for Netherlands, 'no' for Norway, 'pl' for Poland, 'pt' for Portugal, 'ru' for Russia,
  'se' for Sweden, 'uk' for United Kingdom, 'us' for United States... </p>
  <p>You can get more information on <a href='https://www.kelkoogroup.com/kelkoo-customer-service/support-for-merchants/sales-tracking-guides/implement-kelkoo-
sales-tracking/'>https://www.kelkoogroup.com/kelkoo-customer-service/support-for-merchants/sales-tracking-guides/implement-kelkoo-sales-tracking/</a> </p>
  <hr> <p>Optional: </p>
    <p>      Multi merchant information : If you need to configure multiple merchant information (you have multiple Merchant Identifier/Country), you can set configuration as <i>{country: <strong>\"</strong>nl<strong>\"</strong>, merchantId: <strong>\"</strong>123<strong>\"</strong>}, {country: <strong>\"</strong>nb<strong>\"</strong>, merchantId: <strong>\"</strong>345<strong>\"</strong>}</i></p>",
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
