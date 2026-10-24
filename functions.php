<?php
/**
 * Second Sitting child theme.
 *
 * The parent (Twenty Twenty-Five) is a block theme. The branded pages here are
 * classic PHP templates with their own header and footer parts, routed in via
 * the template_include filter so they work regardless of block template edits.
 */

defined( 'ABSPATH' ) || exit;

define( 'SS_VERSION', '1.2.0' );
define( 'SS_DIR', get_stylesheet_directory() );
define( 'SS_URI', get_stylesheet_directory_uri() );

require_once SS_DIR . '/inc/postcode.php';
require_once SS_DIR . '/inc/woocommerce.php';
require_once SS_DIR . '/inc/setup.php';
require_once SS_DIR . '/inc/contact.php';

/**
 * Theme supports.
 */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 1000,
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'ss-primary' => __( 'Second Sitting: main menu', 'second-sitting' ),
		'ss-footer'  => __( 'Second Sitting: footer menu', 'second-sitting' ),
		'ss-legal'   => __( 'Second Sitting: legal links', 'second-sitting' ),
	) );
} );

/**
 * Image URL helper for bundled theme images.
 */
function ss_img( $file ) {
	return esc_url( SS_URI . '/assets/img/' . $file );
}

/**
 * Is the current request rendered by one of our branded templates?
 */
function ss_is_branded_view() {
	return '' !== ss_resolve_template();
}

/**
 * Assets.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'ss-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Poppins:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'ss-main', SS_URI . '/assets/css/main.css', array( 'ss-fonts' ), SS_VERSION );

	wp_enqueue_script( 'ss-main', SS_URI . '/assets/js/main.js', array(), SS_VERSION, true );
	wp_localize_script( 'ss-main', 'ssData', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'ss_postcode' ),
		'saved'    => ss_get_saved_postcode_result(),
		'radiusKm' => SS_DELIVERY_RADIUS_KM,
		'i18n'     => array(
			'checking' => __( 'Checking…', 'second-sitting' ),
			'invalid'  => __( 'We couldn\'t find that postcode. Please check it and try again.', 'second-sitting' ),
			'error'    => __( 'Something went wrong. Please try again.', 'second-sitting' ),
		),
	) );
} );

/**
 * Page templates selectable in the editor.
 */
add_filter( 'theme_page_templates', function ( $templates ) {
	$templates['ss-templates/template-home.php']    = __( 'Second Sitting Home', 'second-sitting' );
	$templates['ss-templates/template-contact.php'] = __( 'Second Sitting Contact / Viewings', 'second-sitting' );
	return $templates;
} );

/**
 * Which Second Sitting template (if any) renders this request.
 */
function ss_resolve_template() {
	$slug = is_page() ? get_page_template_slug() : '';
	// Older installs stored the template under templates/.
	$slug = str_replace( 'templates/template-', 'ss-templates/template-', (string) $slug );

	if ( is_front_page() || 'ss-templates/template-home.php' === $slug ) {
		return 'template-home.php';
	}
	if ( 'ss-templates/template-contact.php' === $slug || ( is_page() && (int) get_option( 'ss_contact_page_id' ) === get_queried_object_id() ) ) {
		return 'template-contact.php';
	}
	if ( function_exists( 'is_woocommerce' ) ) {
		if ( is_product() ) {
			return 'single-product.php';
		}
		if ( is_shop() || is_product_taxonomy() ) {
			return 'archive-product.php';
		}
		if ( is_cart() ) {
			return 'cart.php';
		}
		if ( is_checkout() ) {
			return 'checkout.php';
		}
		if ( is_account_page() ) {
			return 'my-account.php';
		}
	}
	return '';
}

/**
 * Route to our PHP templates.
 *
 * The parent is a block theme, so WordPress and WooCommerce would otherwise render
 * their block templates (with the Twenty Twenty-Five header and default Woo layouts).
 * Running last guarantees ours is the only template output for these views.
 */
add_filter( 'template_include', function ( $template ) {
	// An empty template means something already rendered the page and asked WordPress to stop
	// (e.g. WooCommerce's "Coming soon" page while the store isn't live). Never render a second page.
	if ( '' === $template || false === $template ) {
		return $template;
	}
	$file = ss_resolve_template();
	if ( $file ) {
		// Stop the block template that core already resolved from being rendered anywhere else.
		$GLOBALS['_wp_current_template_content'] = '';
		$GLOBALS['_wp_current_template_id']      = '';
		return SS_DIR . '/ss-templates/' . $file;
	}
	return $template;
}, PHP_INT_MAX );

/**
 * Body class for scoping.
 */
add_filter( 'body_class', function ( $classes ) {
	if ( ss_is_branded_view() ) {
		$classes[] = 'ss-branded';
	}
	return $classes;
} );

/**
 * Customizer: contact details and viewing link.
 */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'ss_details', array(
		'title'    => __( 'Second Sitting', 'second-sitting' ),
		'priority' => 30,
	) );
	$fields = array(
		'ss_address'     => array( __( 'Collection address', 'second-sitting' ), 'sanitize_text_field', '[Collection address], Harrow' ),
		'ss_email'       => array( __( 'Email', 'second-sitting' ), 'sanitize_email', '' ),
		'ss_phone'       => array( __( 'Phone / WhatsApp', 'second-sitting' ), 'sanitize_text_field', '' ),
		'ss_viewing_url' => array( __( '"Book a viewing" link (leave empty to use the Contact page)', 'second-sitting' ), 'esc_url_raw', '' ),
		'ss_hours'       => array( __( 'Viewing hours', 'second-sitting' ), 'sanitize_text_field', 'Mon–Sat 9am–6pm, Sun by appointment' ),
		'ss_map_query'   => array( __( 'Map location (address or postcode for the map)', 'second-sitting' ), 'sanitize_text_field', 'Harrow, London' ),
		'ss_facebook'    => array( __( 'Facebook URL', 'second-sitting' ), 'esc_url_raw', '' ),
		'ss_instagram'   => array( __( 'Instagram URL', 'second-sitting' ), 'esc_url_raw', '' ),
	);
	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting( $id, array( 'default' => $f[2], 'sanitize_callback' => $f[1] ) );
		$wp_customize->add_control( $id, array( 'label' => $f[0], 'section' => 'ss_details', 'type' => 'text' ) );
	}
} );

/**
 * Where "Book a viewing" goes: Customizer link, else the Contact page.
 *
 * @param int $product_id Optional sofa to pre-select on the form.
 */
function ss_viewing_url( $product_id = 0 ) {
	$custom = get_theme_mod( 'ss_viewing_url', '' );
	if ( $custom ) {
		return $custom;
	}
	$page = (int) get_option( 'ss_contact_page_id' );
	$url  = $page && 'publish' === get_post_status( $page ) ? get_permalink( $page ) : home_url( '/contact/' );
	if ( $product_id ) {
		$url = add_query_arg( 'sofa', (int) $product_id, $url );
	}
	return $url . '#viewing';
}

/**
 * Fallback menu used before the client assigns real menus.
 */
function ss_fallback_menu() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$items = array(
		home_url( '/' )                          => __( 'Home', 'second-sitting' ),
		$shop                                    => __( 'Sofas', 'second-sitting' ),
		home_url( '/#collections' )              => __( 'Collections', 'second-sitting' ),
		home_url( '/#about' )                    => __( 'About us', 'second-sitting' ),
		ss_viewing_url()                         => __( 'Viewings & contact', 'second-sitting' ),
	);
	echo '<ul class="ss-menu">';
	foreach ( $items as $url => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
