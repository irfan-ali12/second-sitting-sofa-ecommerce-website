<?php
/**
 * Viewing / contact requests.
 *
 * Every request is emailed to the shop AND saved under "Viewing requests" in the admin,
 * so nothing is lost if the server's email isn't set up yet.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'ss_enquiry', array(
		'labels'          => array(
			'name'          => __( 'Viewing requests', 'second-sitting' ),
			'singular_name' => __( 'Viewing request', 'second-sitting' ),
			'menu_name'     => __( 'Viewing requests', 'second-sitting' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-calendar-alt',
		'menu_position'   => 56,
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

function ss_contact_email() {
	$email = get_theme_mod( 'ss_email', '' );
	return is_email( $email ) ? $email : get_option( 'admin_email' );
}

function ss_handle_enquiry() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'sent', 'ss_error' ), $back );

	if ( ! isset( $_POST['ss_enquiry_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ss_enquiry_nonce'] ) ), 'ss_enquiry' ) ) {
		wp_safe_redirect( add_query_arg( 'ss_error', 'expired', $back ) . '#viewing' );
		exit;
	}
	// Honeypot: real people never fill this.
	if ( ! empty( $_POST['ss_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', 1, $back ) . '#viewing' );
		exit;
	}

	$f = array(
		'type'     => isset( $_POST['ss_type'] ) && 'question' === $_POST['ss_type'] ? 'question' : 'viewing',
		'name'     => sanitize_text_field( wp_unslash( $_POST['ss_name'] ?? '' ) ),
		'email'    => sanitize_email( wp_unslash( $_POST['ss_email'] ?? '' ) ),
		'phone'    => sanitize_text_field( wp_unslash( $_POST['ss_phone'] ?? '' ) ),
		'postcode' => ss_normalise_postcode( sanitize_text_field( wp_unslash( $_POST['ss_postcode'] ?? '' ) ) ),
		'date'     => sanitize_text_field( wp_unslash( $_POST['ss_date'] ?? '' ) ),
		'time'     => sanitize_text_field( wp_unslash( $_POST['ss_time'] ?? '' ) ),
		'sofa'     => absint( $_POST['ss_sofa'] ?? 0 ),
		'message'  => sanitize_textarea_field( wp_unslash( $_POST['ss_message'] ?? '' ) ),
	);

	if ( '' === $f['name'] || ( ! is_email( $f['email'] ) && '' === $f['phone'] ) ) {
		wp_safe_redirect( add_query_arg( 'ss_error', 'missing', $back ) . '#viewing' );
		exit;
	}

	$sofa_name = '';
	if ( $f['sofa'] && function_exists( 'wc_get_product' ) ) {
		$p = wc_get_product( $f['sofa'] );
		if ( $p ) {
			$sofa_name = $p->get_name() . ' (' . get_permalink( $p->get_id() ) . ')';
		}
	}

	$area = '';
	if ( $f['postcode'] ) {
		$check = ss_check_postcode( $f['postcode'] );
		$area  = 'inside' === $check['status'] ? 'within delivery area (' . $check['distance_km'] . ' km)' : ( 'outside' === $check['status'] ? 'outside delivery area (' . $check['distance_km'] . ' km), collection only' : 'postcode not recognised' );
	}

	$lines = array(
		'Request:   ' . ( 'viewing' === $f['type'] ? 'Book a viewing' : 'Question' ),
		'Name:      ' . $f['name'],
		'Email:     ' . $f['email'],
		'Phone:     ' . $f['phone'],
		'Postcode:  ' . $f['postcode'] . ( $area ? ' — ' . $area : '' ),
		'Sofa:      ' . ( $sofa_name ? $sofa_name : 'Not specified' ),
	);
	if ( 'viewing' === $f['type'] ) {
		$lines[] = 'Preferred: ' . trim( $f['date'] . ' ' . $f['time'] );
	}
	$lines[] = '';
	$lines[] = $f['message'];
	$body    = implode( "\n", $lines );

	$title = ( 'viewing' === $f['type'] ? 'Viewing: ' : 'Question: ' ) . $f['name'] . ( $f['date'] ? ' — ' . $f['date'] : '' );

	wp_insert_post( array(
		'post_type'    => 'ss_enquiry',
		'post_status'  => 'private',
		'post_title'   => $title,
		'post_content' => $body,
	) );

	$headers = array();
	if ( is_email( $f['email'] ) ) {
		$headers[] = 'Reply-To: ' . $f['name'] . ' <' . $f['email'] . '>';
	}
	wp_mail( ss_contact_email(), '[Second Sitting] ' . $title, $body, $headers );

	if ( is_email( $f['email'] ) ) {
		wp_mail(
			$f['email'],
			'viewing' === $f['type'] ? 'Your viewing request – Second Sitting' : 'We got your message – Second Sitting',
			"Hi {$f['name']},\n\nThanks for getting in touch. We'll confirm " . ( 'viewing' === $f['type'] ? 'your viewing time' : 'an answer' ) . " shortly, usually the same day.\n\n" . ( $sofa_name ? "Sofa: {$sofa_name}\n\n" : '' ) . "Second Sitting\nGiving sofas another chance."
		);
	}

	wp_safe_redirect( add_query_arg( 'sent', 1, $back ) . '#viewing' );
	exit;
}
add_action( 'admin_post_ss_enquiry', 'ss_handle_enquiry' );
add_action( 'admin_post_nopriv_ss_enquiry', 'ss_handle_enquiry' );
