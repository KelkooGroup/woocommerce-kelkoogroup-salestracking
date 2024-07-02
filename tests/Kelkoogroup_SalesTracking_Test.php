<?php
/**
 * Test case for Kelkoogroup Sales Tracking plugin.
 */
class Kelkoogroup_SalesTracking_Test extends WP_UnitTestCase {

    /**
     * Test if Kelkoogroup_SalesTracking class exists.
     */
    public function test_class_exists() {
        $this->assertTrue( class_exists( 'Kelkoogroup_SalesTracking' ) );
    }

    /**
     * Test if Kelkoogroup_SalesTracking class is instantiable.
     */
    public function test_class_is_instantiable() {
        $kelkoogroup_salestracking = new Kelkoogroup_SalesTracking();
        $this->assertInstanceOf( 'Kelkoogroup_SalesTracking', $kelkoogroup_salestracking );
    }

    /**
     * Tests if identifiers from URL parameters are stored in transients.
     */
    public function test_kelkoogroup_salestracking_store_identifiers_from_url() {
        $_GET = array(
            'kk' => 'test_kk_identifier',
            'gclid' => 'test_gclid_identifier',
            'msclkid' => 'test_msclkid_identifier',
            'other_param' => 'test_other_param'
        );

        kelkoogroup_salestracking_store_identifiers_from_url();

        $this->assertEquals('test_kk_identifier', get_transient('kelkoogroup_salestracking_kk_identifier'));
        $this->assertEquals('test_gclid_identifier', get_transient('kelkoogroup_salestracking_gclid_identifier'));
        $this->assertEquals('test_msclkid_identifier', get_transient('kelkoogroup_salestracking_msclkid_identifier'));

        // Vérifier si les valeurs des autres paramètres ne sont pas stockées dans les transients
        $this->assertFalse(get_transient('kelkoogroup_salestracking_other_param_identifier'));
    }


    /**
     * Test if Kelkoogroup_SalesTracking class methods are callable.
     */
    public function test_class_methods() {
        $kelkoogroup_salestracking = new Kelkoogroup_SalesTracking();

        $this->assertTrue( method_exists( $kelkoogroup_salestracking, 'kelkoogroup_salestracking_setup' ) );
        $this->assertTrue( method_exists( $kelkoogroup_salestracking, 'kelkoogroup_salestracking_woocommerce_thankyou' ) );
        $this->assertTrue( method_exists( $kelkoogroup_salestracking, 'kelkoogroup_salestracking_send_server_side_request' ) );
        $this->assertTrue( method_exists( $kelkoogroup_salestracking, 'kelkoogroup_salestracking_generate_sale_id' ) );
        $this->assertTrue( method_exists( $kelkoogroup_salestracking, 'kelkoogroup_salestracking_encode_basket' ) );
        $this->assertTrue( method_exists( $kelkoogroup_salestracking, 'kelkoogroup_salestracking_custom_base64_encode' ) );
    }

    /**
     * Test if Kelkoogroup_SalesTracking settings initialization works.
     */
    public function test_settings_init() {
        $kelkoogroup_salestracking = new Kelkoogroup_SalesTracking();
        $kelkoogroup_salestracking->kelkoogroup_salestracking_setup();
    
        $this->assertNotFalse(has_action('admin_menu', 'kelkoogroup_salestracking_add_admin_menu'));
        $this->assertNotFalse(has_action('admin_init', 'kelkoogroup_salestracking_settings_init'));
    }

   /**
     * Test if kelkoogroup_salestracking_construct_kelkoogroup_request_url correctly constructs the URL with the given parameters.
     */
    public function test_kelkoogroup_salestracking_construct_kelkoogroup_request_url() {
        $order = $this->createMock(WC_Order::class);
        $order->method('get_order_number')->willReturn('12345');
        $order->method('get_total')->willReturn(100.00);

        $productsKelkoo = array(
            array(
                'productname' => 'Product 1',
                'productid' => 1,
                'quantity' => 2,
                'price' => 10.00
            ),
            array(
                'productname' => 'Product 2',
                'productid' => 2,
                'quantity' => 1,
                'price' => 20.00
            )
        );

        $campaign = array(
            'country' => 'fr',
            'merchantId' => '123'
        );

        // Mock get_transient function to return null for simplicity
        $this->plugin_instance = $this->getMockBuilder(Kelkoogroup_SalesTracking::class)
            ->setMethods(['kelkoogroup_salestracking_encode_basket', 'kelkoogroup_salestracking_generate_sale_id'])
            ->getMock();

        $this->plugin_instance->method('kelkoogroup_salestracking_encode_basket')->willReturn('VGVzdCBkYXRhIGZvciBlbmNvZGluZw');

        // Set up transcient value for the test
        set_transient('kelkoogroup_salestracking_kk_identifier', 'transient_kelkoo_id');

        // Set up cookie values for the test
        $_COOKIE['kelkoogroup_salestracking_gclid_identifier'] = 'cookie_gclid_id';

        $expected_url = 'https://s.kelkoogroup.net/st?country=fr&orderId=12345&comId=123&orderValue=100&productsInfos=VGVzdCBkYXRhIGZvciBlbmNvZGluZw&saleId=0.55&kelkooId=transient_kelkoo_id&gclid=cookie_gclid_id&source=serverToServer&ecommercePlatform=woocommerce';

        $constructed_url = $this->plugin_instance->kelkoogroup_salestracking_construct_kelkoogroup_request_url($order, $productsKelkoo, $campaign, "0.55");

        // Assert that the constructed URL matches the expected URL
        $this->assertEquals($expected_url, $constructed_url);

        // Cleanup transcient and cookies
        unset($_COOKIE['kelkoogroup_salestracking_gclid_identifier']);
        delete_transient('kelkoogroup_salestracking_kk_identifier');
    }

    /**
     * Test if kelkoogroup_salestracking_encode_basket correctly encodes the products array.
     */
    public function test_kelkoogroup_salestracking_encode_basket() {
        $productsArray = array(
            array(
                'productname' => 'Product 1',
                'productid' => 1,
                'quantity' => 2,
                'price' => 10.00
            ),
            array(
                'productname' => 'Product 2',
                'productid' => 2,
                'quantity' => 1,
                'price' => 20.00
            )
        );

        $this->plugin_instance = new Kelkoogroup_SalesTracking();
        $encoded_basket = $this->plugin_instance->kelkoogroup_salestracking_encode_basket($productsArray);

        // Expected encoded basket value
        $expected_json = '[{"productname":"Product 1","productid":1,"quantity":2,"price":10},{"productname":"Product 2","productid":2,"quantity":1,"price":20}]';
        $expected_base64 = 'W3sicHJvZHVjdG5hbWUiOiJQcm9kdWN0IDEiLCJwcm9kdWN0aWQiOjEsInF1YW50aXR5IjoyLCJwcmljZSI6MTB9LHsicHJvZHVjdG5hbWUiOiJQcm9kdWN0IDIiLCJwcm9kdWN0aWQiOjIsInF1YW50aXR5IjoxLCJwcmljZSI6MjB9XQ';
        $expected_encoded_basket = urlencode($expected_base64);

        // Assert that the encoded basket matches the expected value
        $this->assertEquals($expected_encoded_basket, $encoded_basket);
    }

    /**
     * Test if kelkoogroup_salestracking_custom_base64_encode correctly encodes data.
     */
    public function test_kelkoogroup_salestracking_custom_base64_encode() {
        $this->plugin_instance = new Kelkoogroup_SalesTracking();
        $data = 'Test data for encoding';

        $encoded_data = $this->plugin_instance->kelkoogroup_salestracking_custom_base64_encode($data);

        // Expected encoded data using custom base64 encoding
        $expected_data = 'VGVzdCBkYXRhIGZvciBlbmNvZGluZw';
        // Assert that the custom base64 encoded data matches the expected data
        $this->assertEquals($expected_data, $encoded_data);
    }
    
    /**
     * Test if kelkoogroup_salestracking_generate_sale_id returns a float between 0 and 1.
     */
    public function test_kelkoogroup_salestracking_generate_sale_id() {
        $this->plugin_instance = new Kelkoogroup_SalesTracking();

        $sale_id = $this->plugin_instance->kelkoogroup_salestracking_generate_sale_id();
        
        // Assert that the sale ID is a float
        $this->assertIsFloat($sale_id);
        // Assert that the sale ID is between 0 and 1
        $this->assertGreaterThanOrEqual(0, $sale_id);
        $this->assertLessThanOrEqual(1, $sale_id);
    }

}
