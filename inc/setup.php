<?php
/**
 * One-time setup:
 * - On activation: create the Home page (Second Sitting Home template), set it as the front page,
 *   and create the product categories the homepage tiles link to.
 * - Admin notice with a one-click "Import sample sofas" button (demo products using the theme's photos).
 */

defined( 'ABSPATH' ) || exit;

function ss_categories() {
	return array(
		'2-seater'      => '2 Seater',
		'3-seater'      => '3 Seater',
		'corner-sofas'  => 'Corner Sofas',
		'cuddle-chairs' => 'Cuddle Chairs',
		'modular-sofas' => 'Modular Sofas',
		'sofa-sets'     => 'Sofa Sets',
	);
}

/* -------------------------------------------------------------------------
 * Activation
 * ---------------------------------------------------------------------- */

function ss_setup_site() {
	// Home page.
	$home_id = (int) get_option( 'ss_home_page_id' );
	if ( ! $home_id || ! get_post( $home_id ) ) {
		$existing = get_page_by_path( 'home' );
		$home_id  = $existing ? $existing->ID : wp_insert_post( array(
			'post_title'   => 'Home',
			'post_name'    => 'home',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '',
		) );
		update_option( 'ss_home_page_id', $home_id );
	}
	update_post_meta( $home_id, '_wp_page_template', 'ss-templates/template-home.php' );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );

	// Contact / viewings page (target of every "Book a viewing" button).
	$contact_id = (int) get_option( 'ss_contact_page_id' );
	if ( ! $contact_id || ! get_post( $contact_id ) ) {
		$existing   = get_page_by_path( 'contact' );
		$contact_id = $existing ? $existing->ID : wp_insert_post( array(
			'post_title'   => 'Contact & Viewings',
			'post_name'    => 'contact',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '',
		) );
		update_option( 'ss_contact_page_id', $contact_id );
	}
	update_post_meta( $contact_id, '_wp_page_template', 'ss-templates/template-contact.php' );

	// UK store defaults that suit sofas.
	if ( class_exists( 'WooCommerce' ) ) {
		update_option( 'woocommerce_dimension_unit', 'cm' );
		update_option( 'woocommerce_hide_out_of_stock_items', 'no' );
		update_option( 'woocommerce_thumbnail_cropping', 'uncropped' ); // keep whole sofas in cards
	}

	update_option( 'ss_setup_version', SS_VERSION );

	// Categories.
	if ( taxonomy_exists( 'product_cat' ) ) {
		foreach ( ss_categories() as $slug => $name ) {
			if ( ! term_exists( $slug, 'product_cat' ) ) {
				wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
			}
		}
	}
}
add_action( 'after_switch_theme', 'ss_setup_site' );

// Theme updated in place (no re-activation): run the setup once for the new version.
add_action( 'admin_init', function () {
	if ( get_option( 'ss_setup_version' ) !== SS_VERSION && current_user_can( 'manage_options' ) ) {
		ss_setup_site();
	}
} );

/* -------------------------------------------------------------------------
 * Sample products
 * ---------------------------------------------------------------------- */

function ss_sample_products() {
	return array(
		array(
			'key' => 'teal-2', 'name' => 'Teal 2 Seater Sofa', 'brand' => 'DFS · French Connection', 'colour' => 'Teal',
			'seats' => '2 seater', 'cat' => '2-seater', 'price' => 200, 'rrp' => 799, 'img' => '',
			'dims' => array( 180, 95, 85 ),
			'short' => 'A French Connection design from DFS in a rich teal fabric. Firm seat, plump back cushions and solid feet.',
			'condition' => 'Excellent condition from a pet-free and smoke-free home. Cushions cleaned. Very light wear on the base of the right arm (see photos).',
			'bundle' => 'teal-3', 'bundle_price' => 400,
		),
		array(
			'key' => 'teal-3', 'name' => 'Teal 3 Seater Sofa', 'brand' => 'DFS · French Connection', 'colour' => 'Teal',
			'seats' => '3 seater', 'cat' => '3-seater', 'price' => 250, 'rrp' => 999, 'img' => '',
			'dims' => array( 220, 95, 85 ),
			'short' => 'The matching 3 seater to our teal French Connection 2 seater. Buy both together and save.',
			'condition' => 'Excellent condition, same home as the 2 seater. No rips, stains or sagging.',
			'bundle' => 'teal-2', 'bundle_price' => 400,
		),
		array(
			'key' => 'grey-3', 'name' => 'Light Grey 3 Seater Sofa', 'brand' => 'Sofa.com', 'colour' => 'Grey',
			'seats' => '3 seater', 'cat' => '3-seater', 'price' => 280, 'rrp' => 1099, 'img' => 'hero-sofa.png',
			'dims' => array( 210, 98, 88 ),
			'short' => 'Deep, feather-filled seat cushions in soft light grey linen-look fabric on tapered oak legs.',
			'condition' => 'Very good condition. Covers professionally cleaned. Minor creasing to seat cushions as expected.',
		),
		array(
			'key' => 'blue-chaise', 'name' => 'Blue Velvet Chaise Corner Sofa & Storage Footstool', 'brand' => 'DFS', 'colour' => 'Blue',
			'seats' => 'Corner', 'cat' => 'corner-sofas', 'price' => 450, 'rrp' => 1899, 'img' => 'chaise-corner.png',
			'dims' => array( 290, 165, 88 ),
			'short' => 'Plush navy velvet left-hand chaise corner with a matching lift-up storage footstool and orange scatter cushions.',
			'condition' => 'Very good condition. Velvet pile has light brushing marks that are normal for the fabric.',
		),
		array(
			'key' => 'grey-corner', 'name' => 'Grey Corner Sofa & Storage Footstool', 'brand' => 'ScS', 'colour' => 'Grey',
			'seats' => 'Corner', 'cat' => 'corner-sofas', 'price' => 380, 'rrp' => 1499, 'img' => 'corner-couch.png',
			'dims' => array( 260, 220, 90 ),
			'short' => 'Large textured-weave corner sofa with high supportive backs and a big storage footstool.',
			'condition' => 'Excellent condition. Smoke-free home. Footstool lid and hinges work perfectly.',
		),
		array(
			'key' => 'cuddle', 'name' => 'Mink Studded Velvet Cuddle Chair', 'brand' => 'Furniture Village', 'colour' => 'Mink',
			'seats' => 'Cuddle chair', 'cat' => 'cuddle-chairs', 'price' => 150, 'rrp' => 649, 'img' => 'cuddle-chair.png',
			'dims' => array( 125, 95, 85 ),
			'short' => 'Oversized crushed-velvet snuggle chair with stud detailing, wide arms and two scatter cushions.',
			'condition' => 'Good condition with natural crushed-velvet texture. One small mark under the seat cushion.',
			'sold' => true,
		),
		array(
			'key' => 'modular', 'name' => 'Blue-Grey Modular Sofa, Armchair & Pouffe', 'brand' => 'Loaf', 'colour' => 'Blue',
			'seats' => 'Sofa set', 'cat' => 'modular-sofas', 'price' => 520, 'rrp' => 2199, 'img' => 'modular-set.png',
			'dims' => array( 300, 200, 80 ),
			'short' => 'Cloud-soft modular corner, matching loveseat armchair and square pouffe in a dusty blue-grey velvet.',
			'condition' => 'Like new. Bought last year, moving house. All modules connect securely.',
		),
		array(
			'key' => 'suite', 'name' => 'Grey 2 Seater & Two Armchairs Suite', 'brand' => 'DFS', 'colour' => 'Grey',
			'seats' => 'Sofa set', 'cat' => 'sofa-sets', 'price' => 350, 'rrp' => 1599, 'img' => 'sofa-suite.png',
			'dims' => array( 190, 95, 88 ),
			'short' => 'Three-piece suite in pale grey weave: a 2 seater sofa and two matching armchairs on oak block feet.',
			'condition' => 'Very good condition. Dimensions shown are for the sofa; armchairs are 105cm wide.',
		),
	);
}

/**
 * Make sure a global attribute (pa_brand / pa_colour) exists and is usable this request.
 */
function ss_ensure_attribute( $slug, $label ) {
	$tax = wc_attribute_taxonomy_name( $slug );
	if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
		wc_create_attribute( array( 'name' => $label, 'slug' => $slug, 'type' => 'select', 'has_archives' => false ) );
		delete_transient( 'wc_attribute_taxonomies' );
	}
	if ( ! taxonomy_exists( $tax ) ) {
		register_taxonomy( $tax, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
	}
	return $tax;
}

function ss_build_attribute( $slug, $label, $value ) {
	$tax  = ss_ensure_attribute( $slug, $label );
	$term = term_exists( $value, $tax );
	if ( ! $term ) {
		$term = wp_insert_term( $value, $tax );
	}
	if ( is_wp_error( $term ) ) {
		return null;
	}
	$attr = new WC_Product_Attribute();
	$attr->set_id( wc_attribute_taxonomy_id_by_name( $slug ) );
	$attr->set_name( $tax );
	$attr->set_options( array( (int) $term['term_id'] ) );
	$attr->set_visible( true );
	$attr->set_variation( false );
	return $attr;
}

/**
 * Copy a bundled theme image into the media library (once).
 */
function ss_import_image( $file, $title ) {
	$existing = get_posts( array( 'post_type' => 'attachment', 'meta_key' => '_ss_sample_file', 'meta_value' => $file, 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( $existing ) {
		return (int) $existing[0];
	}
	$path = SS_DIR . '/assets/img/' . $file;
	if ( ! file_exists( $path ) ) {
		return 0;
	}
	$upload = wp_upload_bits( 'second-sitting-' . $file, null, file_get_contents( $path ) ); // phpcs:ignore
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$id = wp_insert_attachment( array(
		'post_mime_type' => 'image/png',
		'post_title'     => $title,
		'post_status'    => 'inherit',
	), $upload['file'] );
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	update_post_meta( $id, '_ss_sample_file', $file );
	return $id;
}

function ss_import_samples() {
	ss_setup_site();
	$ids = array();

	foreach ( ss_sample_products() as $s ) {
		$found = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'meta_key' => '_ss_sample', 'meta_value' => $s['key'], 'numberposts' => 1, 'fields' => 'ids' ) );
		$product = $found ? wc_get_product( $found[0] ) : new WC_Product_Simple();

		$product->set_name( $s['name'] );
		$product->set_status( 'publish' );
		$product->set_regular_price( (string) $s['rrp'] );
		$product->set_sale_price( (string) $s['price'] );
		$product->set_manage_stock( false );
		$product->set_stock_status( empty( $s['sold'] ) ? 'instock' : 'outofstock' );
		$product->set_sold_individually( true );
		$product->set_short_description( $s['short'] );
		$product->set_width( (string) $s['dims'][0] );
		$product->set_length( (string) $s['dims'][1] );
		$product->set_height( (string) $s['dims'][2] );

		$term = get_term_by( 'slug', $s['cat'], 'product_cat' );
		if ( $term ) {
			$product->set_category_ids( array( $term->term_id ) );
		}

		$attrs = array_filter( array(
			ss_build_attribute( 'brand', 'Brand', $s['brand'] ),
			ss_build_attribute( 'colour', 'Colour', $s['colour'] ),
		) );
		$product->set_attributes( $attrs );

		if ( $s['img'] ) {
			$img = ss_import_image( $s['img'], $s['name'] );
			if ( $img ) {
				$product->set_image_id( $img );
			}
		}

		$product->update_meta_data( '_ss_sample', $s['key'] );
		$product->update_meta_data( '_ss_brand', $s['brand'] );
		$product->update_meta_data( '_ss_seats', $s['seats'] );
		$product->update_meta_data( '_ss_condition', $s['condition'] );
		$ids[ $s['key'] ] = $product->save();

		if ( ! empty( $s['sold'] ) ) {
			update_post_meta( $ids[ $s['key'] ], '_ss_sold_at', time() - DAY_IN_SECONDS );
		}
	}

	// Bundles need both IDs.
	foreach ( ss_sample_products() as $s ) {
		if ( ! empty( $s['bundle'] ) && isset( $ids[ $s['key'] ], $ids[ $s['bundle'] ] ) ) {
			update_post_meta( $ids[ $s['key'] ], '_ss_bundle_with', $ids[ $s['bundle'] ] );
			update_post_meta( $ids[ $s['key'] ], '_ss_bundle_price', $s['bundle_price'] );
		}
	}

	update_option( 'ss_samples_imported', time() );
	return count( $ids );
}

/* -------------------------------------------------------------------------
 * Admin notice + handler
 * ---------------------------------------------------------------------- */

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	if ( isset( $_GET['ss_imported'] ) ) { // phpcs:ignore
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
			esc_html( sprintf( __( 'Second Sitting: %d sample sofas imported and the homepage is set.', 'second-sitting' ), absint( $_GET['ss_imported'] ) ) ), // phpcs:ignore
			esc_url( home_url( '/' ) ),
			esc_html__( 'View the site', 'second-sitting' )
		);
		return;
	}
	if ( get_option( 'ss_samples_imported' ) || get_option( 'ss_samples_dismissed' ) ) {
		return;
	}
	$import  = wp_nonce_url( admin_url( 'admin-post.php?action=ss_import_samples' ), 'ss_import_samples' );
	$dismiss = wp_nonce_url( admin_url( 'admin-post.php?action=ss_dismiss_samples' ), 'ss_import_samples' );
	printf(
		'<div class="notice notice-info"><p><strong>Second Sitting:</strong> %s</p><p><a class="button button-primary" href="%s">%s</a> <a class="button" href="%s">%s</a></p></div>',
		esc_html__( 'Load 8 sample sofas (with photos, RRP savings, a bundle offer and a sold item) so you can preview the shop, product and checkout pages.', 'second-sitting' ),
		esc_url( $import ),
		esc_html__( 'Import sample sofas', 'second-sitting' ),
		esc_url( $dismiss ),
		esc_html__( 'No thanks', 'second-sitting' )
	);
} );

add_action( 'admin_post_ss_import_samples', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ss_import_samples' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'second-sitting' ) );
	}
	$count = ss_import_samples();
	wp_safe_redirect( add_query_arg( 'ss_imported', $count, admin_url( 'edit.php?post_type=product' ) ) );
	exit;
} );

add_action( 'admin_post_ss_dismiss_samples', function () {
	if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ss_import_samples' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'second-sitting' ) );
	}
	update_option( 'ss_samples_dismissed', 1 );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
} );

/**
 * Tools > Second Sitting: run setup / import again any time.
 */
add_action( 'admin_menu', function () {
	add_management_page( 'Second Sitting', 'Second Sitting', 'manage_options', 'second-sitting', function () {
		$import = wp_nonce_url( admin_url( 'admin-post.php?action=ss_import_samples' ), 'ss_import_samples' );
		echo '<div class="wrap"><h1>Second Sitting</h1>';
		echo '<p>' . esc_html__( 'Creates the Home page (set as your homepage), the product categories, and 8 sample sofas. Running it again updates the samples instead of duplicating them.', 'second-sitting' ) . '</p>';
		printf( '<p><a class="button button-primary" href="%s">%s</a></p></div>', esc_url( $import ), esc_html__( 'Set up homepage & import sample sofas', 'second-sitting' ) );
	} );
} );

/**
 * WooCommerce "Coming soon" mode hides the shop, product, cart and checkout pages from visitors.
 */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || 'yes' !== get_option( 'woocommerce_coming_soon' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p><strong>Second Sitting:</strong> %s <a href="%s">%s</a></p></div>',
		esc_html__( 'Your store is in WooCommerce "Coming soon" mode, so visitors who aren\'t logged in see a placeholder instead of the shop, sofa pages, cart and checkout.', 'second-sitting' ),
		esc_url( admin_url( 'admin.php?page=wc-settings&tab=site-visibility' ) ),
		esc_html__( 'Make the store live', 'second-sitting' )
	);
} );
