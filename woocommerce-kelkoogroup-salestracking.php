<?php
/**
 * Plugin Name:       Kelkoogroup Sales Tracking
 * Description:       Plugin to contain Kelkoogroup sales tracking customisation for Woocommerce
 * Plugin URI:        https://github.com/KelkooGroup/woocommerce-kelkoogroup-salestracking
 * Version:           2.1.0

 * Author:            Kelkoo Group
 * Author URI:        https://www.kelkoogroup.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 3.0.0
 * Requires PHP:      7.4
 * Tested up to:      7.0
 * WC requires at least: 3.0.0
 * WC tested up to:   10.9
 *
 * @package Kelkoogroup_SalesTracking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Declare compatibility with WooCommerce's High-Performance Order Storage (custom order tables).
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Main Kelkoogroup_SalesTracking Class
 *
 * @class Kelkoogroup_SalesTracking
 * @version	2.1.0
 * @since 1.0.0
 * @package	Kelkoogroup_SalesTracking
 */
class Kelkoogroup_SalesTracking {

	/**
	 * Set up the plugin
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'kelkoogroup_salestracking_setup' ), -1 );
		require_once( 'inc/functions.php' );
		require_once( 'admin/class-kelkoogroup-salestracking-admin.php');
	}

     /**
      * Setup all the things
      */
    public function kelkoogroup_salestracking_setup() {
            add_action( 'admin_menu', 'kelkoogroup_salestracking_add_admin_menu' );
            add_action( 'admin_init', 'kelkoogroup_salestracking_settings_init' );
            add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'kelkoogroup_action_links' );
            add_action('woocommerce_thankyou', array(&$this, 'kelkoogroup_salestracking_woocommerce_thankyou'), -10);
    }


    public function kelkoogroup_salestracking_woocommerce_thankyou($orderId) {
    if( class_exists( 'WC_Order' ) ) {
        $order=new WC_Order($orderId);
        if ( $order ) :
            // Guard against woocommerce_thankyou firing on every render of the order-received
            // page (back navigation, refresh, tab left open), which was sending the sale
            // multiple times.
            if ( $order->get_meta( '_kelkoogroup_salestracking_tracked', true ) ) {
                return;
            }
            $options = get_option( 'kelkoogroup_salestracking_settings' );
            $productsKelkoo=array();
            $items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ));
            foreach ( $items as $item ) {
                $product = $item->get_product();
                $productKelkoo=array('productname'=>$product->get_name(),
               'productid'=>$product->get_id(),
               'quantity'=>$item->get_quantity(),
               'price'=>$product->get_price());
                array_push($productsKelkoo,$productKelkoo);
            }
         ?>
         <script type="text/javascript">
             _kkstrack = {
	      <?php if ($options['kelkoogroup_salestracking_multicomid'] == FALSE) { ?>
	       merchantInfo: [{ country:"<?php echo esc_js( $options['kelkoogroup_salestracking_country'] );?>", merchantId:"<?php echo esc_js( $options['kelkoogroup_salestracking_comid'] );?>" }],
              <?php } else { ?>
               merchantInfo: [<?php echo wp_strip_all_tags( $options['kelkoogroup_salestracking_multicomid'] );?>],
              <?php } ?>
	       orderValue: '<?php echo esc_js( $order ->get_total());?>',
               orderId: '<?php echo esc_js( $order ->get_order_number());?>',
               basket: <?php echo wp_strip_all_tags( json_encode($productsKelkoo) );?>
            };
             (function() {
               var s = document.createElement('script');
               s.type = 'text/javascript';
               s.async = true;
               s.src = 'https://s.kk-resources.com/ks.js';
               var x = document.getElementsByTagName('script')[0];
               x.parentNode.insertBefore(s, x);
             })();
          </script>
          <?php
            // Direct server-side tracking with non-blocking HTTP request
            $this->kelkoogroup_salestracking_send_server_side_request($options, $order, $productsKelkoo);

            $order->update_meta_data( '_kelkoogroup_salestracking_tracked', true );
            $order->save();
        endif;
         }
    }


    /**
      * Function to send the sale with server2server call
    */
    function kelkoogroup_salestracking_send_server_side_request($options, $order, $productsKelkoo) {
        $headers = array(
            'Referer' => wp_get_referer()
        );
    
        $campaigns = array();
        $multicomid = $options['kelkoogroup_salestracking_multicomid'];
        if ($multicomid) {
            $multicomid_json = preg_replace('/([{,]\s*)([\'"])?(\w+)([\'"])?:/','$1"$3":', $multicomid);
            $multicomid_array = json_decode('['.$multicomid_json.']', true);
            foreach ($multicomid_array as $campaign) {
                $campaigns[] = array(
                    'country' => $campaign['country'],
                    'merchantId' => $campaign['merchantId']
                );
            }
        } else {
            $campaigns[] = array(
                'country' => $options['kelkoogroup_salestracking_country'],
                'merchantId' => $options['kelkoogroup_salestracking_comid']
            );
        }
        $saleId = $this->kelkoogroup_salestracking_generate_sale_id();
    
        // Send server call for each campaign
        foreach ($campaigns as $campaign) {
            $request_url = $this->kelkoogroup_salestracking_construct_kelkoogroup_request_url($order, $productsKelkoo, $campaign, $saleId);
            $response = wp_remote_get($request_url, array(
                'headers' => $headers,
                'blocking' => false,
                'timeout' => 0.7 
            ));
    
            // Check errors
            if (is_wp_error($response)) {
                error_log('Error during HTTP request : ' . $response->get_error_message());
            }
        }
    }

    // Function to generate the sale id
    function kelkoogroup_salestracking_generate_sale_id() {
      return mt_rand() / mt_getrandmax();
    }

    // Function to encode basket items
    function kelkoogroup_salestracking_encode_basket($productsArray) {
        // Convert the PHP array to JSON
        $jsonData = json_encode($productsArray);

        // Encode the JSON in base64 without padding
        $base64Data = $this->kelkoogroup_salestracking_custom_base64_encode($jsonData);

        // URL encode the result
        $urlEncodedData = urlencode($base64Data);

        return $urlEncodedData;
    }

    // Function to custom base64 encode data
    function kelkoogroup_salestracking_custom_base64_encode($data) {
      return strtr(base64_encode($data), '+/', '-_');
  }

    // Function to validate a click identifier (kelkooId, gclid, msclkid) before it is used to
    // build a URL with add_query_arg(), which does not encode its values.
    function kelkoogroup_salestracking_sanitize_identifier($value) {
        if ( empty( $value ) || ! is_string( $value ) ) {
            return null;
        }
        return preg_match( '/^[\w.-]+$/', $value ) ? $value : null;
    }

     /**
     * Construct the URL for the Kelkoogroup request
     */
    function kelkoogroup_salestracking_construct_kelkoogroup_request_url($order, $productsKelkoo, $campaign, $saleId) {

      $kelkoo_id = $gclid_id = $msclkid_id = null;

      if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        // Get identifiers from user meta
        $kelkoo_id = get_user_meta($user_id, 'kelkoogroup_salestracking_kelkooId', true);
        $gclid_id = get_user_meta($user_id, 'kelkoogroup_salestracking_kk_gclid', true);
        $msclkid_id = get_user_meta($user_id, 'kelkoogroup_salestracking_kk_msclkid', true);
      }

      // Fallback to cookies if user meta are not found
      if (!$kelkoo_id) {
        $kelkoo_id = isset($_COOKIE['kelkooId']) ? $_COOKIE['kelkooId'] : null;
      }
      if (!$gclid_id) {
        $gclid_id = isset($_COOKIE['kk_gclid']) ? $_COOKIE['kk_gclid'] : null;
      }
      if (!$msclkid_id) {
          $msclkid_id = isset($_COOKIE['kk_msclkid']) ? $_COOKIE['kk_msclkid'] : null;
      }

      // Identifiers come from user meta or cookies, both attacker-controlled: add_query_arg()
      // does not urlencode its values, so an unvalidated value could inject extra query
      // parameters into the tracking request. Reject anything that isn't a plausible click id.
      $kelkoo_id = $this->kelkoogroup_salestracking_sanitize_identifier($kelkoo_id);
      $gclid_id = $this->kelkoogroup_salestracking_sanitize_identifier($gclid_id);
      $msclkid_id = $this->kelkoogroup_salestracking_sanitize_identifier($msclkid_id);

      $url = 'https://s.kelkoogroup.net/st';

      $params = array(
          'country' => $campaign['country'],
          'orderId' => $order->get_order_number(),
          'comId' => $campaign['merchantId'],
          'orderValue' => $order->get_total(),
          'productsInfos' => $this->kelkoogroup_salestracking_encode_basket($productsKelkoo),
          'saleId' => $saleId,
          'kelkooId' => $kelkoo_id ?: null,
          'gclid' => $gclid_id ?: null,
          'msclkid' => $msclkid_id ?: null,
          'source' => 'serverToServer',
          'ecommercePlatform' => 'woocommerce',
          'plgVersion' => '2.1.0'
      );

      return add_query_arg($params, $url);
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
