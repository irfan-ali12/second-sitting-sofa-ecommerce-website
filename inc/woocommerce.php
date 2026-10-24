<?php
/**
 * WooCommerce behaviour for Second Sitting.
 *
 * - "Reserve this sofa" wording, postcode check required before reserving.
 * - Product fields: brand, seat size, condition notes, bundle partner + bundle price.
 * - Bundle discount applied in the cart when both pieces are in it.
 * - Sold items stay visible in the shop for a few days as social proof.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

if ( ! defined( 'SS_SOLD_VISIBLE_DAYS' ) ) {
	define( 'SS_SOLD_VISIBLE_DAYS', 5 );
}

/* -------------------------------------------------------------------------
 * Product data
 * ---------------------------------------------------------------------- */

function ss_product_brand( $product ) {
	$brand = $product->get_meta( '_ss_brand' );
	if ( ! $brand ) {
		$brand = $product->get_attribute( 'pa_brand' );
	}
	return $brand;
}

function ss_product_seats( $product ) {
	$seats = $product->get_meta( '_ss_seats' );
	if ( ! $seats ) {
		$seats = $product->get_attribute( 'pa_seats' );
	}
	return $seats;
}

/**
 * Our price = sale price, RRP = regular price. Returns null if no RRP saving.
 */
function ss_price_parts( $product ) {
	$regular = (float) $product->get_regular_price();
	$price   = (float) $product->get_price();
	return array(
		'price'  => $price,
		'rrp'    => $regular > $price ? $regular : null,
		'saving' => $regular > $price ? $regular - $price : null,
	);
}

function ss_price_html( $product, $size = 'card' ) {
	$p = ss_price_parts( $product );
	$out  = '<div class="ss-price ss-price--' . esc_attr( $size ) . '">';
	$out .= '<span class="ss-price-now">' . wc_price( $p['price'] ) . '</span>';
	if ( $p['rrp'] ) {
		$out .= '<span class="ss-price-rrp"><span class="screen-reader-text">' . esc_html__( 'RRP', 'second-sitting' ) . '</span>' . ( 'single' === $size ? esc_html__( 'RRP ', 'second-sitting' ) : '' ) . '<del>' . wc_price( $p['rrp'] ) . '</del></span>';
		$out .= '<span class="ss-price-save">' . sprintf( esc_html__( 'Save %s', 'second-sitting' ), wp_strip_all_tags( wc_price( $p['saving'] ) ) ) . '</span>';
	}
	$out .= '</div>';
	return $out;
}

/**
 * Admin fields on the product "General" tab.
 */
add_action( 'woocommerce_product_options_general_product_data', function () {
	echo '<div class="options_group">';
	woocommerce_wp_text_input( array(
		'id'          => '_ss_brand',
		'label'       => __( 'Brand', 'second-sitting' ),
		'placeholder' => 'DFS · French Connection',
		'desc_tip'    => true,
		'description' => __( 'Shown above the product name.', 'second-sitting' ),
	) );
	woocommerce_wp_select( array(
		'id'      => '_ss_seats',
		'label'   => __( 'Seat size', 'second-sitting' ),
		'options' => array(
			''          => __( '—', 'second-sitting' ),
			'Armchair'  => 'Armchair',
			'Cuddle chair' => 'Cuddle chair',
			'2 seater'  => '2 seater',
			'3 seater'  => '3 seater',
			'4 seater'  => '4 seater',
			'Corner'    => 'Corner',
			'Sofa set'  => 'Sofa set',
		),
	) );
	woocommerce_wp_textarea_input( array(
		'id'          => '_ss_condition',
		'label'       => __( 'Condition notes', 'second-sitting' ),
		'placeholder' => __( 'e.g. Pet- and smoke-free home. Light wear on the left arm (photo 5).', 'second-sitting' ),
	) );
	echo '</div><div class="options_group">';
	woocommerce_wp_text_input( array(
		'id'          => '_ss_bundle_with',
		'label'       => __( 'Bundle: matching product ID', 'second-sitting' ),
		'type'        => 'number',
		'desc_tip'    => true,
		'description' => __( 'ID of the matching piece (e.g. the 3 seater). Leave empty for no bundle.', 'second-sitting' ),
	) );
	woocommerce_wp_text_input( array(
		'id'          => '_ss_bundle_price',
		'label'       => __( 'Bundle price (both pieces)', 'second-sitting' ) . ' (' . get_woocommerce_currency_symbol() . ')',
		'data_type'   => 'price',
	) );
	echo '</div>';
} );

add_action( 'woocommerce_admin_process_product_object', function ( $product ) {
	foreach ( array( '_ss_brand', '_ss_seats', '_ss_bundle_with', '_ss_bundle_price' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$product->update_meta_data( $key, wc_clean( wp_unslash( $_POST[ $key ] ) ) ); // phpcs:ignore
		}
	}
	if ( isset( $_POST['_ss_condition'] ) ) { // phpcs:ignore
		$product->update_meta_data( '_ss_condition', sanitize_textarea_field( wp_unslash( $_POST['_ss_condition'] ) ) ); // phpcs:ignore
	}
} );

/* -------------------------------------------------------------------------
 * Wording & reserve rules
 * ---------------------------------------------------------------------- */

add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
	return __( 'Reserve this sofa', 'second-sitting' );
} );
add_filter( 'woocommerce_product_add_to_cart_text', function ( $text, $product ) {
	return $product->is_in_stock() ? __( 'View sofa', 'second-sitting' ) : __( 'Sold', 'second-sitting' );
}, 10, 2 );
add_filter( 'woocommerce_order_button_text', function () {
	return __( 'Reserve my sofa', 'second-sitting' );
} );

/**
 * Archive cards link to the product page (postcode check happens there).
 */
add_filter( 'woocommerce_loop_add_to_cart_link', function ( $html, $product ) {
	return sprintf(
		'<a href="%s" class="ss-btn ss-btn--outline ss-card-cta">%s</a>',
		esc_url( $product->get_permalink() ),
		esc_html( $product->add_to_cart_text() )
	);
}, 10, 2 );

/**
 * Stock is one-off: never more than one of each in the cart.
 */
add_filter( 'woocommerce_is_sold_individually', '__return_true' );

/**
 * Postcode must be checked before a sofa can be reserved.
 */
add_filter( 'woocommerce_add_to_cart_validation', function ( $passed ) {
	if ( ! ss_get_saved_postcode_result() ) {
		wc_add_notice( __( 'Please check your postcode first so we can confirm delivery or collection.', 'second-sitting' ), 'error' );
		return false;
	}
	return $passed;
} );

/**
 * Note shown under the order button.
 */
add_action( 'woocommerce_review_order_after_submit', function () {
	echo '<p class="ss-reserve-note">' . esc_html__( 'Your sofa is held for you. We\'ll call or message to confirm the delivery slot before taking payment.', 'second-sitting' ) . '</p>';
} );

/* -------------------------------------------------------------------------
 * Bundle offer
 * ---------------------------------------------------------------------- */

function ss_get_bundle( $product ) {
	$partner_id = absint( $product->get_meta( '_ss_bundle_with' ) );
	$bundle     = (float) $product->get_meta( '_ss_bundle_price' );
	if ( ! $partner_id || ! $bundle ) {
		return null;
	}
	$partner = wc_get_product( $partner_id );
	if ( ! $partner || ! $partner->is_in_stock() || ! $product->is_in_stock() ) {
		return null;
	}
	$separate = (float) $product->get_price() + (float) $partner->get_price();
	if ( $bundle >= $separate ) {
		return null;
	}
	return array(
		'partner'  => $partner,
		'price'    => $bundle,
		'separate' => $separate,
		'saving'   => $separate - $bundle,
	);
}

/**
 * ?ss_bundle=<partner_id> on add-to-cart adds the matching piece as well.
 */
add_action( 'woocommerce_add_to_cart', function ( $key, $product_id ) {
	static $running = false;
	if ( $running || empty( $_REQUEST['ss_bundle'] ) ) { // phpcs:ignore
		return;
	}
	$partner_id = absint( $_REQUEST['ss_bundle'] ); // phpcs:ignore
	$product    = wc_get_product( $product_id );
	if ( ! $product || absint( $product->get_meta( '_ss_bundle_with' ) ) !== $partner_id ) {
		return;
	}
	$running = true;
	WC()->cart->add_to_cart( $partner_id );
	$running = false;
}, 10, 2 );

/**
 * Discount when both bundle pieces are in the cart.
 */
add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$ids  = array();
	foreach ( $cart->get_cart() as $item ) {
		$ids[] = (int) $item['product_id'];
	}
	$done = array();
	foreach ( $ids as $id ) {
		if ( in_array( $id, $done, true ) ) {
			continue;
		}
		$product = wc_get_product( $id );
		if ( ! $product ) {
			continue;
		}
		$partner_id = absint( $product->get_meta( '_ss_bundle_with' ) );
		$bundle     = (float) $product->get_meta( '_ss_bundle_price' );
		if ( ! $partner_id || ! $bundle || ! in_array( $partner_id, $ids, true ) ) {
			continue;
		}
		$partner  = wc_get_product( $partner_id );
		$separate = (float) $product->get_price() + (float) $partner->get_price();
		if ( $bundle < $separate ) {
			$cart->add_fee( __( 'Bundle saving', 'second-sitting' ), -1 * ( $separate - $bundle ) );
		}
		$done[] = $id;
		$done[] = $partner_id;
	}
} );

/* -------------------------------------------------------------------------
 * Sold items: badge + visible for SS_SOLD_VISIBLE_DAYS, then hidden
 * ---------------------------------------------------------------------- */

add_action( 'woocommerce_product_set_stock_status', function ( $product_id, $status ) {
	if ( 'outofstock' === $status ) {
		update_post_meta( $product_id, '_ss_sold_at', time() );
	} else {
		delete_post_meta( $product_id, '_ss_sold_at' );
	}
}, 10, 2 );

add_action( 'woocommerce_product_query', function ( $q ) {
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
		return; // Shop already hides sold items.
	}
	$meta   = (array) $q->get( 'meta_query' );
	$meta[] = array(
		'relation' => 'OR',
		array( 'key' => '_stock_status', 'value' => 'outofstock', 'compare' => '!=' ),
		array( 'key' => '_ss_sold_at', 'value' => time() - SS_SOLD_VISIBLE_DAYS * DAY_IN_SECONDS, 'compare' => '>=', 'type' => 'NUMERIC' ),
	);
	$q->set( 'meta_query', $meta );
	// Available sofas first, sold ones after (unless the shopper picked a sort order).
	if ( ! empty( $_GET['orderby'] ) ) { // phpcs:ignore
		return;
	}
	$q->set( 'orderby', array( 'meta_value' => 'ASC', 'date' => 'DESC' ) );
	$q->set( 'meta_key', '_stock_status' );
} );

function ss_sold_label( $product ) {
	$sold_at = (int) $product->get_meta( '_ss_sold_at' );
	if ( ! $sold_at ) {
		return __( 'Sold', 'second-sitting' );
	}
	$created = $product->get_date_created() ? $product->get_date_created()->getTimestamp() : $sold_at;
	$days    = max( 1, (int) ceil( ( $sold_at - $created ) / DAY_IN_SECONDS ) );
	/* translators: %d: days on sale */
	return sprintf( _n( 'Sold in %d day', 'Sold in %d days', $days, 'second-sitting' ), $days );
}

/* -------------------------------------------------------------------------
 * Misc
 * ---------------------------------------------------------------------- */

// Our templates render their own wrappers and breadcrumbs.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_filter( 'loop_shop_per_page', function () {
	return 12;
} );

// Cart page: keep the shipping estimate (it shows the postcode states too).
add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' );
add_filter( 'woocommerce_shipping_calculator_enable_state', '__return_false' );
