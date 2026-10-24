<?php
/**
 * Shop and product category pages.
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header', null, array( 'variant' => 'inner' ) );

$shop_url = wc_get_page_permalink( 'shop' );
$current  = is_product_category() ? get_queried_object() : null;
$base_url = $current ? get_term_link( $current ) : $shop_url;

// Category chips: top-level product categories (create "2 seater", "3 seater", "Corner sofas" etc.).
$cats = get_terms( array(
	'taxonomy'   => 'product_cat',
	'parent'     => 0,
	'hide_empty' => true,
	'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
) );

/**
 * Attribute dropdown for WooCommerce's native ?filter_<attr>= query.
 */
$attr_select = function ( $attr, $label ) {
	$tax = wc_attribute_taxonomy_name( $attr );
	if ( ! taxonomy_exists( $tax ) ) {
		return;
	}
	$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => true ) );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return;
	}
	$key = 'filter_' . $attr;
	$sel = isset( $_GET[ $key ] ) ? sanitize_title( wp_unslash( $_GET[ $key ] ) ) : ''; // phpcs:ignore
	printf( '<label class="ss-filter"><span>%s</span><select name="%s" onchange="this.form.submit()">', esc_html( $label ), esc_attr( $key ) );
	printf( '<option value="">%s</option>', esc_html__( 'Any', 'second-sitting' ) );
	foreach ( $terms as $t ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $t->slug ), selected( $sel, $t->slug, false ), esc_html( $t->name ) );
	}
	echo '</select></label>';
};

$price_ranges = array(
	''        => __( 'Any price', 'second-sitting' ),
	'0-250'   => __( 'Under £250', 'second-sitting' ),
	'250-500' => __( '£250 – £500', 'second-sitting' ),
	'500-1000'=> __( '£500 – £1,000', 'second-sitting' ),
	'1000-'   => __( '£1,000+', 'second-sitting' ),
);
$min = isset( $_GET['min_price'] ) ? absint( $_GET['min_price'] ) : ''; // phpcs:ignore
$max = isset( $_GET['max_price'] ) ? absint( $_GET['max_price'] ) : ''; // phpcs:ignore
$current_range = ( '' === $min && '' === $max ) ? '' : $min . '-' . ( $max ? $max : '' );
?>

<main id="ss-main" class="ss-page">
	<section class="ss-page-hero">
		<div class="wrap">
			<?php woocommerce_breadcrumb( array( 'wrap_before' => '<nav class="ss-crumbs" aria-label="Breadcrumb">', 'wrap_after' => '</nav>' ) ); ?>
			<h1><?php echo $current ? esc_html( $current->name ) : esc_html__( 'In stock now', 'second-sitting' ); ?></h1>
			<p><?php esc_html_e( 'One-off premium sofas. When they\'re gone, they\'re gone. Delivered within 70km of Harrow.', 'second-sitting' ); ?></p>
		</div>
	</section>

	<div class="wrap ss-shop">
		<?php woocommerce_output_all_notices(); ?>

		<?php if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) : ?>
		<nav class="ss-chips" aria-label="<?php esc_attr_e( 'Sofa type', 'second-sitting' ); ?>">
			<a class="ss-chip<?php echo $current ? '' : ' is-active'; ?>" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'All', 'second-sitting' ); ?></a>
			<?php foreach ( $cats as $c ) : ?>
				<a class="ss-chip<?php echo ( $current && $current->term_id === $c->term_id ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php endif; ?>

		<form class="ss-filters" method="get" action="<?php echo esc_url( $base_url ); ?>">
			<?php $attr_select( 'brand', __( 'Brand', 'second-sitting' ) ); ?>
			<?php $attr_select( 'colour', __( 'Colour', 'second-sitting' ) ); ?>
			<label class="ss-filter"><span><?php esc_html_e( 'Price', 'second-sitting' ); ?></span>
				<select data-ss-price>
					<?php foreach ( $price_ranges as $val => $label ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_range, $val ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<input type="hidden" name="min_price" value="<?php echo esc_attr( $min ); ?>">
			<input type="hidden" name="max_price" value="<?php echo esc_attr( $max ); ?>">
			<?php if ( ! empty( $_GET ) ) : // phpcs:ignore ?>
				<a class="ss-filter-clear" href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Clear filters', 'second-sitting' ); ?></a>
			<?php endif; ?>
			<noscript><button class="ss-btn ss-btn--solid" type="submit"><?php esc_html_e( 'Filter', 'second-sitting' ); ?></button></noscript>
		</form>

		<?php if ( woocommerce_product_loop() ) : ?>
			<ul class="products ss-grid">
				<?php
				while ( have_posts() ) {
					the_post();
					wc_get_template_part( 'content', 'product' );
				}
				?>
			</ul>
			<?php woocommerce_pagination(); ?>
		<?php else : ?>
			<div class="ss-empty">
				<h2><?php esc_html_e( 'Nothing here right now', 'second-sitting' ); ?></h2>
				<p><?php esc_html_e( 'New sofas come in every week and sell fast. Follow us on Facebook to see them first.', 'second-sitting' ); ?></p>
				<a class="ss-btn ss-btn--solid" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'See all sofas', 'second-sitting' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php get_template_part( 'parts/footer' ); ?>
