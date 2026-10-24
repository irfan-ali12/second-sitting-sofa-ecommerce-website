<?php
/**
 * Template Name: Second Sitting Home
 *
 * Homepage built from the "Home page – desktop" design.
 * The page's own content (if any) replaces the About Us text.
 */
defined( 'ABSPATH' ) || exit;

$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

/**
 * Link to a product category by slug, falling back to the shop.
 */
$cat_link = function ( $slug ) use ( $shop ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return $shop;
};

$viewing = ss_viewing_url();

get_template_part( 'parts/header', null, array( 'variant' => 'home' ) );
?>

<main id="ss-main">

<!-- ============ HERO ============ -->
<section class="hero" aria-labelledby="ss-hero-title">
	<div class="hero-curve" aria-hidden="true"></div>
	<div class="hero-tri" aria-hidden="true"></div>
	<div class="hero-tri hero-tri--line" aria-hidden="true"></div>

	<div class="hero-lamp" aria-hidden="true">
		<span class="hero-lamp-cord"></span>
		<img src="<?php echo ss_img( 'pendant-light.png' ); ?>" alt="" width="453" height="551">
		<span class="hero-lamp-glow"></span>
	</div>

	<div class="wrap">
		<div class="hero-grid">
			<div class="hero-media">
				<img src="<?php echo ss_img( 'hero-sofa.png' ); ?>" alt="<?php esc_attr_e( 'Light grey three-seater sofa with oak legs', 'second-sitting' ); ?>" width="874" height="352" fetchpriority="high">
			</div>
			<div class="hero-copy">
				<h1 id="ss-hero-title"><?php esc_html_e( 'Feel the luxury, second time round', 'second-sitting' ); ?></h1>
				<p><?php esc_html_e( 'Premium brand sofas like DFS and French Connection at a fraction of the RRP, delivered across London and the Home Counties.', 'second-sitting' ); ?></p>
				<div class="hero-ctas">
					<a class="ss-btn ss-btn--outline" href="<?php echo esc_url( $viewing ); ?>"><?php esc_html_e( 'Book a viewing', 'second-sitting' ); ?></a>
					<a class="ss-btn ss-btn--solid" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Browse sofas', 'second-sitting' ); ?>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg></a>
				</div>
			</div>
		</div>

		<div class="hero-pc">
			<?php ss_postcode_form( 'hero' ); ?>
		</div>

		<div class="stats">
			<div class="stat stat--first">
				<span class="stat-dots" aria-hidden="true"><span></span><span></span><span></span></span>
				<span><strong>12K+</strong><span><?php esc_html_e( 'Facebook followers', 'second-sitting' ); ?></span></span>
			</div>
			<div class="stat"><strong>70km</strong><span><?php esc_html_e( 'Delivery radius from Harrow', 'second-sitting' ); ?></span></div>
			<div class="stat"><strong><?php esc_html_e( 'Next day', 'second-sitting' ); ?></strong><span><?php esc_html_e( 'Delivery time', 'second-sitting' ); ?></span></div>
		</div>
	</div>
</section>

<!-- ============ COLLECTIONS ============ -->
<section id="collections" class="collections">
	<div class="wrap">
		<h2 class="section-title"><?php esc_html_e( 'Our', 'second-sitting' ); ?> <span><?php esc_html_e( 'Collections', 'second-sitting' ); ?></span></h2>

		<div class="coll-wrap">
			<div class="coll-top">
				<a class="coll-card coll-card--a" href="<?php echo esc_url( $cat_link( 'corner-sofas' ) ); ?>">
					<h3><?php esc_html_e( 'Chaise Corner', 'second-sitting' ); ?><br><?php esc_html_e( 'Sofas', 'second-sitting' ); ?></h3>
					<img src="<?php echo ss_img( 'chaise-corner.png' ); ?>" alt="<?php esc_attr_e( 'Blue velvet chaise corner sofa with storage footstool', 'second-sitting' ); ?>" loading="lazy" width="926" height="439">
				</a>
				<div class="coll-card coll-card--tall">
					<a class="coll-pill" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Explore more', 'second-sitting' ); ?>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 17l5-5-5-5M13 17l5-5-5-5"/></svg></a>
				</div>
				<a class="coll-card coll-card--wide" href="<?php echo esc_url( $cat_link( 'corner-sofas' ) ); ?>">
					<h3><?php esc_html_e( 'Modern', 'second-sitting' ); ?><br><?php esc_html_e( 'Corner Couches', 'second-sitting' ); ?></h3>
				</a>
			</div>
			<img class="couch-img" src="<?php echo ss_img( 'corner-couch.png' ); ?>" alt="<?php esc_attr_e( 'Grey textured corner sofa with large storage footstool', 'second-sitting' ); ?>" loading="lazy" width="893" height="509">
		</div>

		<div class="tri">
			<?php
			$tiles = array(
				array( 'cuddle-chair.png', __( 'Cuddle Chairs', 'second-sitting' ), 'cuddle-chairs', __( 'Mink crushed-velvet studded cuddle chair', 'second-sitting' ), 805, 493 ),
				array( 'modular-set.png', __( 'Modular Sets', 'second-sitting' ), 'modular-sofas', __( 'Blue-grey modular sofa set with armchair and pouffe', 'second-sitting' ), 908, 278 ),
				array( 'sofa-suite.png', __( 'Sofa & Chair Suites', 'second-sitting' ), 'sofa-sets', __( 'Grey two-seater sofa with two matching armchairs', 'second-sitting' ), 910, 252 ),
			);
			foreach ( $tiles as $t ) :
				?>
				<div class="tri-card">
					<img src="<?php echo ss_img( $t[0] ); ?>" alt="<?php echo esc_attr( $t[3] ); ?>" loading="lazy" width="<?php echo (int) $t[4]; ?>" height="<?php echo (int) $t[5]; ?>">
					<h3><?php echo esc_html( $t[1] ); ?></h3>
					<a class="ss-btn ss-btn--white" href="<?php echo esc_url( $cat_link( $t[2] ) ); ?>"><?php esc_html_e( 'Explore more', 'second-sitting' ); ?>
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 17l5-5-5-5M13 17l5-5-5-5"/></svg></a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php
// Just-in strip: latest available sofas.
if ( function_exists( 'wc_get_products' ) ) :
	$latest = wc_get_products( array( 'limit' => 4, 'status' => 'publish', 'stock_status' => 'instock', 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( $latest ) :
		?>
<section class="just-in">
	<div class="wrap">
		<div class="just-in-head">
			<h2 class="section-title section-title--left"><?php esc_html_e( 'Just', 'second-sitting' ); ?> <span><?php esc_html_e( 'in', 'second-sitting' ); ?></span></h2>
			<a class="ss-btn ss-btn--outline" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'View all sofas', 'second-sitting' ); ?></a>
		</div>
		<ul class="products ss-grid">
			<?php
			foreach ( $latest as $p ) {
				$GLOBALS['product'] = $p; // phpcs:ignore
				$GLOBALS['post']    = get_post( $p->get_id() ); // phpcs:ignore
				setup_postdata( $GLOBALS['post'] );
				wc_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
			?>
		</ul>
	</div>
</section>
		<?php
	endif;
endif;
?>

<!-- ============ ABOUT ============ -->
<section id="about" class="about-sec">
	<div class="wrap">
		<h2 class="section-title section-title--brand"><?php esc_html_e( 'About Us', 'second-sitting' ); ?></h2>
		<div class="about">
			<div class="about-copy">
				<?php
				$content = '';
				if ( is_page() ) {
					$content = trim( (string) get_post_field( 'post_content', get_queried_object_id() ) );
				}
				if ( $content ) {
					echo apply_filters( 'the_content', $content ); // phpcs:ignore
				} else {
					?>
					<p class="about-lead"><?php esc_html_e( 'Second Sitting gives good sofas another chance. We source premium brand sofas from names like DFS and French Connection, check them over, photograph every angle honestly and sell them at a fraction of the original price.', 'second-sitting' ); ?></p>
					<p><?php esc_html_e( 'We\'re based in Harrow, North West London, and deliver same or next day within 70km. Viewings are always welcome, so come and sit on it before you buy.', 'second-sitting' ); ?></p>
					<?php
				}
				?>
				<div class="about-cta">
					<a class="ss-btn ss-btn--solid" href="<?php echo esc_url( $viewing ); ?>"><?php esc_html_e( 'Book a viewing', 'second-sitting' ); ?>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg></a>
				</div>
			</div>
			<div class="about-art" aria-hidden="true">
				<div class="about-wall"></div>
				<div class="about-frame"></div>
				<img class="about-lamp" src="<?php echo ss_img( 'pendant-light.png' ); ?>" alt="">
				<div class="about-floor"></div>
				<img class="about-sofa" src="<?php echo ss_img( 'chaise-corner.png' ); ?>" alt="" loading="lazy">
			</div>
		</div>
	</div>
</section>

</main>

<?php get_template_part( 'parts/footer' ); ?>
