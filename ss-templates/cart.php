<?php
/** Cart page ("Your reserved sofas"). */
defined( 'ABSPATH' ) || exit;
get_template_part( 'ss-templates/woo-page', null, array(
	'slug'      => 'cart',
	'shortcode' => 'woocommerce_cart',
	'title'     => __( 'Your reserved sofas', 'second-sitting' ),
	'intro'     => __( 'Each sofa is one of a kind. Check out soon to keep it.', 'second-sitting' ),
	'step'      => 2,
) );
