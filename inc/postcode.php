<?php
/**
 * Delivery radius check: UK postcodes within 70km of Harrow.
 *
 * - Geocodes postcodes with the free postcodes.io API (no key needed), cached for 30 days.
 * - Front end: AJAX check used by the homepage hero and product pages.
 * - Checkout: shows the "we deliver" / "collect from Harrow" message, limits
 *   out-of-area orders to collection, and blocks delivery orders that fail the check.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'SS_DELIVERY_RADIUS_KM' ) ) {
	define( 'SS_DELIVERY_RADIUS_KM', 70 );
}

/**
 * Harrow base point. Replace with the exact collection address coordinates
 * via the filter, e.g. add_filter( 'ss_base_location', fn() => array( 51.58, -0.34 ) );
 */
function ss_base_location() {
	return apply_filters( 'ss_base_location', array( 51.5806, -0.3420 ) );
}

function ss_normalise_postcode( $postcode ) {
	$pc = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', (string) $postcode ) );
	if ( strlen( $pc ) > 4 ) {
		$pc = substr( $pc, 0, -3 ) . ' ' . substr( $pc, -3 );
	}
	return $pc;
}

/**
 * Is this a valid UK postcode (full, e.g. "HA1 2AB", or district only, e.g. "HA1")?
 */
function ss_is_uk_postcode( $pc ) {
	return (bool) preg_match( '/^(GIR 0AA|[A-Z]{1,2}[0-9][A-Z0-9]?( [0-9][A-Z]{2})?)$/', $pc );
}

/**
 * Bundled centroid table (outcodes for the whole UK, sectors around London).
 */
function ss_postcode_table() {
	static $table = null;
	if ( null === $table ) {
		$table = include __DIR__ . '/postcode-data.php';
	}
	return $table;
}

/**
 * Look up lat/lng for a UK postcode.
 *
 * 1. Bundled data: postcode sector (e.g. "HA1 2"), then district (e.g. "HA1"). Works offline,
 *    accurate to roughly 1–2 km inside London, so no external service is needed.
 * 2. postcodes.io for anything not in the table (cached 30 days).
 *
 * @return array|null array( lat, lng ) or null if not a recognisable UK postcode.
 */
function ss_geocode_postcode( $postcode ) {
	$pc = ss_normalise_postcode( $postcode );
	if ( '' === $pc || ! ss_is_uk_postcode( $pc ) ) {
		return null;
	}

	$table   = ss_postcode_table();
	$parts   = explode( ' ', $pc );
	$outcode = $parts[0];

	if ( isset( $parts[1] ) ) {
		$sector = $outcode . ' ' . $parts[1][0];
		if ( isset( $table['sectors'][ $sector ] ) ) {
			return $table['sectors'][ $sector ];
		}
	}
	if ( isset( $table['outcodes'][ $outcode ] ) ) {
		return $table['outcodes'][ $outcode ];
	}

	// New or unusual postcode: ask postcodes.io.
	$key    = 'ss_geo_' . md5( $pc );
	$cached = get_transient( $key );
	if ( false !== $cached ) {
		return 'none' === $cached ? null : $cached;
	}
	$url = isset( $parts[1] )
		? 'https://api.postcodes.io/postcodes/' . rawurlencode( $pc )
		: 'https://api.postcodes.io/outcodes/' . rawurlencode( $pc );
	$res = wp_remote_get( $url, array( 'timeout' => 6 ) );
	if ( is_wp_error( $res ) ) {
		return null;
	}
	$body   = json_decode( wp_remote_retrieve_body( $res ), true );
	$coords = null;
	if ( isset( $body['result']['latitude'], $body['result']['longitude'] ) ) {
		$coords = array( (float) $body['result']['latitude'], (float) $body['result']['longitude'] );
	}
	set_transient( $key, $coords ? $coords : 'none', $coords ? 30 * DAY_IN_SECONDS : HOUR_IN_SECONDS );
	return $coords;
}

function ss_distance_km( $lat1, $lng1, $lat2, $lng2 ) {
	$r    = 6371;
	$dlat = deg2rad( $lat2 - $lat1 );
	$dlng = deg2rad( $lng2 - $lng1 );
	$a    = sin( $dlat / 2 ) ** 2 + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $dlng / 2 ) ** 2;
	return $r * 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
}

/**
 * Check a postcode against the delivery radius.
 *
 * @return array { status: 'inside'|'outside'|'invalid', postcode, distance_km }
 */
function ss_check_postcode( $postcode, $country = 'GB' ) {
	$pc = ss_normalise_postcode( $postcode );

	if ( $country && 'GB' !== strtoupper( $country ) ) {
		return array( 'status' => 'outside', 'postcode' => $pc, 'distance_km' => null );
	}

	$coords = ss_geocode_postcode( $pc );
	if ( ! $coords ) {
		return array( 'status' => 'invalid', 'postcode' => $pc, 'distance_km' => null );
	}

	list( $blat, $blng ) = ss_base_location();
	$km = ss_distance_km( $blat, $blng, $coords[0], $coords[1] );

	return array(
		'status'      => $km <= SS_DELIVERY_RADIUS_KM ? 'inside' : 'outside',
		'postcode'    => $pc,
		'distance_km' => round( $km ),
	);
}

/**
 * Remember the shopper's last checked postcode (WooCommerce session).
 */
function ss_save_postcode_result( $result, $update_customer = true ) {
	if ( function_exists( 'WC' ) && WC()->session ) {
		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}
		WC()->session->set( 'ss_postcode', $result );

		if ( $update_customer && 'invalid' !== $result['status'] && WC()->customer ) {
			WC()->customer->set_shipping_postcode( $result['postcode'] );
			WC()->customer->set_billing_postcode( $result['postcode'] );
			WC()->customer->save();
		}
	}
}

function ss_get_saved_postcode_result() {
	if ( function_exists( 'WC' ) && WC()->session ) {
		$r = WC()->session->get( 'ss_postcode' );
		return is_array( $r ) ? $r : null;
	}
	return null;
}

/**
 * AJAX: check a postcode.
 */
function ss_ajax_check_postcode() {
	check_ajax_referer( 'ss_postcode', 'nonce' );
	$postcode = isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '';
	$result   = ss_check_postcode( $postcode );
	if ( 'invalid' !== $result['status'] ) {
		ss_save_postcode_result( $result );
	}
	$result['html'] = ss_postcode_message_html( $result );
	wp_send_json_success( $result );
}
add_action( 'wp_ajax_ss_check_postcode', 'ss_ajax_check_postcode' );
add_action( 'wp_ajax_nopriv_ss_check_postcode', 'ss_ajax_check_postcode' );

/**
 * The two customer-facing states.
 */
function ss_postcode_message_html( $result ) {
	if ( empty( $result ) || 'invalid' === $result['status'] ) {
		return '';
	}
	$pc = esc_html( $result['postcode'] );

	if ( 'inside' === $result['status'] ) {
		return '<div class="ss-pc-result is-inside" role="status">'
			. '<span class="ss-pc-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12l5 5 9-10"/></svg></span>'
			. '<div><strong>' . esc_html__( 'Great news, we deliver to you', 'second-sitting' ) . '</strong>'
			. '<span>' . sprintf( esc_html__( '%s is within our delivery area. Same-day or next-day delivery available, or collect from Harrow.', 'second-sitting' ), $pc ) . '</span></div>'
			. '</div>';
	}

	return '<div class="ss-pc-result is-outside" role="status">'
		. '<span class="ss-pc-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.5-7-12a7 7 0 0114 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg></span>'
		. '<div><strong>' . esc_html__( 'We don\'t deliver to your area yet', 'second-sitting' ) . '</strong>'
		. '<span>' . sprintf( esc_html__( 'But you\'re welcome to collect from Harrow. %s is outside our 70km delivery area.', 'second-sitting' ), $pc ) . '</span></div>'
		. '</div>';
}

/**
 * Reusable postcode checker form.
 *
 * @param string $variant 'hero' | 'product' | 'compact'
 */
function ss_postcode_form( $variant = 'product' ) {
	$saved = ss_get_saved_postcode_result();
	$id    = 'ss-pc-' . wp_unique_id();
	?>
	<form class="ss-pc ss-pc--<?php echo esc_attr( $variant ); ?>" data-ss-postcode novalidate>
		<label class="<?php echo 'hero' === $variant ? 'screen-reader-text' : 'ss-pc-label'; ?>" for="<?php echo esc_attr( $id ); ?>">
			<?php esc_html_e( 'Check if we deliver to you', 'second-sitting' ); ?>
		</label>
		<div class="ss-pc-field">
			<svg class="ss-pc-pin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.5-7-12a7 7 0 0114 0c0 5.5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
			<input id="<?php echo esc_attr( $id ); ?>" type="text" name="postcode" autocomplete="postal-code" inputmode="text"
				placeholder="<?php echo esc_attr( 'hero' === $variant ? __( 'Enter your postcode – do we deliver to you?', 'second-sitting' ) : __( 'Your postcode, e.g. HA1 2AB', 'second-sitting' ) ); ?>"
				value="<?php echo $saved ? esc_attr( $saved['postcode'] ) : ''; ?>">
			<button type="submit"><?php esc_html_e( 'Check', 'second-sitting' ); ?></button>
		</div>
		<div class="ss-pc-output" aria-live="polite"><?php echo $saved ? ss_postcode_message_html( $saved ) : ''; // phpcs:ignore ?></div>
	</form>
	<?php
}

/* -------------------------------------------------------------------------
 * Checkout integration
 * ---------------------------------------------------------------------- */

function ss_is_pickup_rate( $rate ) {
	$method = is_object( $rate ) ? $rate->get_method_id() : (string) $rate;
	return in_array( $method, array( 'local_pickup', 'pickup_location', 'ss_collect' ), true )
		|| 0 === strpos( (string) $method, 'local_pickup' );
}

/**
 * Inside the radius: show the configured delivery rates.
 * Outside: remove everything except collection, adding a free "Collect from Harrow"
 * rate if the shop hasn't set one up.
 */
add_filter( 'woocommerce_package_rates', function ( $rates, $package ) {
	$dest = isset( $package['destination'] ) ? $package['destination'] : array();
	if ( empty( $dest['postcode'] ) ) {
		return $rates;
	}

	$result = ss_check_postcode( $dest['postcode'], isset( $dest['country'] ) ? $dest['country'] : 'GB' );
	if ( 'invalid' !== $result['status'] ) {
		ss_save_postcode_result( $result, false );
	}

	$has_pickup = false;
	foreach ( $rates as $rate ) {
		if ( ss_is_pickup_rate( $rate ) ) {
			$has_pickup = true;
			break;
		}
	}

	if ( 'outside' === $result['status'] ) {
		foreach ( $rates as $id => $rate ) {
			if ( ! ss_is_pickup_rate( $rate ) ) {
				unset( $rates[ $id ] );
			}
		}
	}

	if ( ! $has_pickup ) {
		$rates['ss_collect'] = new WC_Shipping_Rate(
			'ss_collect',
			__( 'Collect from Harrow (free)', 'second-sitting' ),
			0,
			array(),
			'ss_collect'
		);
	}

	return $rates;
}, 20, 2 );

/**
 * Status message above the shipping options (refreshed with the order review).
 */
add_action( 'woocommerce_review_order_before_shipping', function () {
	$saved    = null;
	$customer = WC()->customer;
	if ( $customer && $customer->get_shipping_postcode() ) {
		$saved = ss_check_postcode( $customer->get_shipping_postcode(), $customer->get_shipping_country() );
	}
	if ( ! $saved || 'invalid' === $saved['status'] ) {
		echo '<tr class="ss-pc-row"><td colspan="2"><div class="ss-pc-result is-pending">'
			. esc_html__( 'Enter your delivery postcode above and we\'ll confirm whether we can deliver to you.', 'second-sitting' )
			. '</div></td></tr>';
		return;
	}
	echo '<tr class="ss-pc-row"><td colspan="2">' . ss_postcode_message_html( $saved ) . '</td></tr>'; // phpcs:ignore
} );

/**
 * Hard rule: no delivery orders outside the radius or with an unverifiable postcode.
 */
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	$chosen   = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods' ) : array();
	$chosen   = reset( $chosen );
	$pickup   = $chosen && ss_is_pickup_rate( strtok( $chosen, ':' ) );
	$ship_to  = ! empty( $data['ship_to_different_address'] );
	$postcode = $ship_to ? $data['shipping_postcode'] : $data['billing_postcode'];
	$country  = $ship_to ? $data['shipping_country'] : $data['billing_country'];

	if ( $pickup ) {
		return;
	}

	$result = ss_check_postcode( $postcode, $country );

	if ( 'invalid' === $result['status'] ) {
		$errors->add( 'ss_postcode', __( 'We couldn\'t verify your delivery postcode. Please check it, or choose "Collect from Harrow".', 'second-sitting' ) );
	} elseif ( 'outside' === $result['status'] ) {
		$errors->add( 'ss_postcode', __( 'We don\'t deliver to your area yet, but you\'re welcome to collect from Harrow. Please choose the collection option to continue.', 'second-sitting' ) );
	}
}, 10, 2 );

/**
 * Pre-fill checkout postcode with the one checked on the site.
 */
add_filter( 'woocommerce_checkout_get_value', function ( $value, $input ) {
	if ( $value || ! in_array( $input, array( 'billing_postcode', 'shipping_postcode' ), true ) ) {
		return $value;
	}
	$saved = ss_get_saved_postcode_result();
	return $saved ? $saved['postcode'] : $value;
}, 10, 2 );

/**
 * Save the outcome on the order so the owner sees it in the admin.
 */
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	$pc     = $order->get_shipping_postcode() ? $order->get_shipping_postcode() : $order->get_billing_postcode();
	$result = ss_check_postcode( $pc, $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country() );
	$order->update_meta_data( '_ss_delivery_check', $result['status'] );
	if ( null !== $result['distance_km'] ) {
		$order->update_meta_data( '_ss_distance_km', $result['distance_km'] );
	}
} );

add_action( 'woocommerce_admin_order_data_after_shipping_address', function ( $order ) {
	$status = $order->get_meta( '_ss_delivery_check' );
	if ( ! $status ) {
		return;
	}
	$km = $order->get_meta( '_ss_distance_km' );
	printf(
		'<p><strong>%s</strong> %s%s</p>',
		esc_html__( 'Delivery check:', 'second-sitting' ),
		esc_html( 'inside' === $status ? __( 'Within 70km of Harrow', 'second-sitting' ) : ucfirst( $status ) ),
		$km !== '' ? esc_html( ' (' . $km . ' km)' ) : ''
	);
} );
