<?php
/**
 * Site header.
 *
 * @var array $args { variant: 'home' (curved hero header) | 'inner' (green band) }
 */
defined( 'ABSPATH' ) || exit;

$variant   = isset( $args['variant'] ) ? $args['variant'] : 'inner';
$cart_url  = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '';
$count     = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$acct_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#ss-main"><?php esc_html_e( 'Skip to content', 'second-sitting' ); ?></a>
<div class="ss">

<div class="ss-topbar ss-topbar--<?php echo esc_attr( $variant ); ?>">
	<div class="wrap ss-topbar-inner">
		<a class="ss-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Second Sitting home', 'second-sitting' ); ?>">
			<span class="ss-logo-mark">SS</span>
			<span class="ss-logo-text">
				<span class="ss-logo-name">SECOND SITTING</span>
				<span class="ss-logo-tag"><?php esc_html_e( 'Giving sofas another chance.', 'second-sitting' ); ?></span>
			</span>
		</a>

		<button class="ss-menu-toggle" type="button" aria-expanded="false" aria-controls="ss-nav" aria-label="<?php esc_attr_e( 'Open menu', 'second-sitting' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
		</button>

		<nav id="ss-nav" class="ss-nav" aria-label="<?php esc_attr_e( 'Main', 'second-sitting' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'ss-primary',
				'container'      => false,
				'menu_class'     => 'ss-menu',
				'fallback_cb'    => 'ss_fallback_menu',
				'depth'          => 1,
			) );
			?>
			<span class="ss-nav-actions">
				<a class="ss-btn ss-btn--outline" href="<?php echo esc_url( $acct_url ); ?>">
					<?php echo is_user_logged_in() ? esc_html__( 'My account', 'second-sitting' ) : esc_html__( 'Log in', 'second-sitting' ); ?>
				</a>
				<?php if ( $cart_url ) : ?>
				<a class="ss-btn ss-btn--solid ss-cart-link" href="<?php echo esc_url( $cart_url ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 8h14l-1.5 12h-11zM9 8V6a3 3 0 016 0v2"/></svg>
					<?php esc_html_e( 'Reserved', 'second-sitting' ); ?>
					<span class="ss-cart-count"><?php echo (int) $count; ?></span>
				</a>
				<?php endif; ?>
			</span>
		</nav>
	</div>
</div>
