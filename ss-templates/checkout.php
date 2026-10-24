<?php
/** Checkout page. The postcode check runs on the delivery postcode (see inc/postcode.php). */
defined( 'ABSPATH' ) || exit;
$received = function_exists( 'is_order_received_page' ) && is_order_received_page();
get_template_part( 'ss-templates/woo-page', null, array(
	'slug'      => $received ? 'thanks' : 'checkout',
	'shortcode' => 'woocommerce_checkout',
	'title'     => $received ? __( 'Your sofa is reserved', 'second-sitting' ) : __( 'Reserve your sofa', 'second-sitting' ),
	'intro'     => $received
		? __( 'Thank you. We\'ll be in touch shortly to confirm your delivery or collection slot.', 'second-sitting' )
		: __( 'We deliver within 70km of Harrow. Outside that area, collection is free.', 'second-sitting' ),
	'step'      => $received ? 3 : 2,
) );
