<?php
/**
 * Product card (shop, categories, homepage "Just in").
 *
 * Overrides woocommerce/templates/content-product.php
 *
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$sold  = ! $product->is_in_stock();
$brand = ss_product_brand( $product );
$seats = ss_product_seats( $product );
// Transparent cut-outs (PNG) sit inside the frame; photos fill it.
$cut   = 'image/png' === get_post_mime_type( $product->get_image_id() );
?>
<li <?php wc_product_class( 'ss-card' . ( $sold ? ' is-sold' : '' ), $product ); ?>>
	<a class="ss-card-link" href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<div class="ss-card-media<?php echo $cut ? ' is-cutout' : ''; ?>">
			<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); // phpcs:ignore ?>
			<?php if ( $sold ) : ?>
				<span class="ss-badge ss-badge--sold"><?php esc_html_e( 'Sold', 'second-sitting' ); ?></span>
			<?php elseif ( $product->get_meta( '_ss_bundle_with' ) ) : ?>
				<span class="ss-badge"><?php esc_html_e( 'Bundle offer', 'second-sitting' ); ?></span>
			<?php endif; ?>
		</div>
		<div class="ss-card-body">
			<?php if ( $brand ) : ?>
				<span class="ss-card-brand"><?php echo esc_html( $brand ); ?></span>
			<?php endif; ?>
			<h3 class="ss-card-title"><?php echo esc_html( $product->get_name() ); ?></h3>
			<?php if ( $seats ) : ?>
				<span class="ss-card-seats"><?php echo esc_html( $seats ); ?></span>
			<?php endif; ?>
			<?php if ( $sold ) : ?>
				<span class="ss-card-sold"><?php echo esc_html( ss_sold_label( $product ) ); ?></span>
			<?php else : ?>
				<?php echo ss_price_html( $product, 'card' ); // phpcs:ignore ?>
			<?php endif; ?>
		</div>
	</a>
</li>
