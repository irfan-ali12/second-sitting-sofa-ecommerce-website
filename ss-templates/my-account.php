<?php
/** My Account page. */
defined( 'ABSPATH' ) || exit;
get_template_part( 'ss-templates/woo-page', null, array(
	'slug'      => 'account',
	'shortcode' => 'woocommerce_my_account',
	'title'     => is_user_logged_in() ? __( 'My account', 'second-sitting' ) : __( 'Log in', 'second-sitting' ),
	'intro'     => '',
	'step'      => 0,
) );
