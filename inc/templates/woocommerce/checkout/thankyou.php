<?php

defined( 'ABSPATH' ) || exit;
require_once(ABSPATH.'wp-content/plugins/woocommerce/templates/checkout/thankyou.php' );

if ( $order ) :
    $options = get_option( 'kelkoogroup_salestracking_settings' );
    $productsKelkoo=array();
    $items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ));
    foreach ( $items as $item ) {
        $product = json_decode($item->get_product());
        $productKelkoo=array('productname'=>$product->name,
       'productid'=>$product->id,
       'quantity'=>$item->get_quantity(),
       'price'=>$product->price);
        array_push($productsKelkoo,$productKelkoo);
    }
?>

<script type="text/javascript">
    _kkstrack = {
      merchantInfo: [{ country:"<?php echo $options['kelkoogroup_salestracking_country'];?>", merchantId:"<?php echo $options['kelkoogroup_salestracking_comid'];?>" }],
      orderValue: '<?php echo $order->get_total();?>',
      orderId: '<?php echo $order->get_order_number();?>',
      basket: [<?php echo json_encode($productsKelkoo);?>]
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

<?php endif; ?>