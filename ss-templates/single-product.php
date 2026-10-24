<?php
/**
 * Single product page.
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header', null, array( 'variant' => 'inner' ) );

while ( have_posts() ) :
	the_post();
	global $product;
	$product = wc_get_product( get_the_ID() );

	$sold      = ! $product->is_in_stock();
	$brand     = ss_product_brand( $product );
	$seats     = ss_product_seats( $product );
	$condition = $product->get_meta( '_ss_condition' );
	$bundle    = ss_get_bundle( $product );
	$viewing   = ss_viewing_url( $product->get_id() );

	$image_ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
	?>

<main id="ss-main" class="ss-page ss-single">
	<div class="wrap">
		<?php woocommerce_breadcrumb( array( 'wrap_before' => '<nav class="ss-crumbs ss-crumbs--dark" aria-label="Breadcrumb">', 'wrap_after' => '</nav>' ) ); ?>
		<?php woocommerce_output_all_notices(); ?>

		<div class="ss-product">
			<!-- Gallery: every photo shown up front, same crop on every shot -->
			<div class="ss-gallery" data-ss-gallery>
				<div class="ss-gallery-main">
					<?php if ( $image_ids ) : ?>
						<?php foreach ( $image_ids as $i => $id ) : ?>
							<figure class="ss-slide<?php echo 0 === $i ? ' is-active' : ''; ?><?php echo 'image/png' === get_post_mime_type( $id ) ? ' is-cutout' : ''; ?>" data-index="<?php echo (int) $i; ?>">
								<?php echo wp_get_attachment_image( $id, 'woocommerce_single', false, array( 'loading' => 0 === $i ? 'eager' : 'lazy' ) ); ?>
							</figure>
						<?php endforeach; ?>
					<?php else : ?>
						<figure class="ss-slide is-active"><?php echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore ?></figure>
					<?php endif; ?>
					<?php if ( $sold ) : ?><span class="ss-badge ss-badge--sold ss-badge--lg"><?php esc_html_e( 'Sold', 'second-sitting' ); ?></span><?php endif; ?>
					<?php if ( count( $image_ids ) > 1 ) : ?>
						<span class="ss-gallery-count"><span data-ss-current>1</span> / <?php echo count( $image_ids ); ?></span>
						<button type="button" class="ss-gallery-nav ss-gallery-prev" aria-label="<?php esc_attr_e( 'Previous photo', 'second-sitting' ); ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg></button>
						<button type="button" class="ss-gallery-nav ss-gallery-next" aria-label="<?php esc_attr_e( 'Next photo', 'second-sitting' ); ?>"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg></button>
					<?php endif; ?>
				</div>
				<?php if ( count( $image_ids ) > 1 ) : ?>
				<div class="ss-thumbs">
					<?php foreach ( $image_ids as $i => $id ) : ?>
						<button type="button" class="ss-thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-index="<?php echo (int) $i; ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show photo %d', 'second-sitting' ), $i + 1 ) ); ?>">
							<?php echo wp_get_attachment_image( $id, 'woocommerce_gallery_thumbnail' ); ?>
						</button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
				<p class="ss-gallery-note"><?php esc_html_e( 'Every photo is here: front, side, back, cushions and any marks.', 'second-sitting' ); ?></p>
			</div>

			<!-- Summary -->
			<div class="ss-summary">
				<?php if ( $brand ) : ?><span class="ss-eyebrow"><?php echo esc_html( $brand ); ?></span><?php endif; ?>
				<h1 class="ss-product-title"><?php the_title(); ?></h1>

				<?php if ( $sold ) : ?>
					<p class="ss-sold-note"><?php echo esc_html( ss_sold_label( $product ) ); ?>. <?php esc_html_e( 'New sofas arrive every week.', 'second-sitting' ); ?></p>
				<?php else : ?>
					<?php echo ss_price_html( $product, 'single' ); // phpcs:ignore ?>
				<?php endif; ?>

				<ul class="ss-tags">
					<?php if ( $seats ) : ?><li><?php echo esc_html( $seats ); ?></li><?php endif; ?>
					<li><?php esc_html_e( 'Next-day delivery', 'second-sitting' ); ?></li>
					<li><?php esc_html_e( 'Viewings welcome', 'second-sitting' ); ?></li>
					<?php if ( ! $sold ) : ?><li><?php esc_html_e( 'Only one', 'second-sitting' ); ?></li><?php endif; ?>
				</ul>

				<?php if ( $product->get_short_description() ) : ?>
					<div class="ss-short"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
				<?php endif; ?>

				<?php if ( $bundle && ! $sold ) : ?>
				<section class="ss-bundle" aria-labelledby="ss-bundle-title">
					<span class="ss-eyebrow ss-eyebrow--accent"><?php esc_html_e( 'Complete the set', 'second-sitting' ); ?></span>
					<h2 id="ss-bundle-title"><?php printf( esc_html__( 'Matching %s available', 'second-sitting' ), esc_html( $bundle['partner']->get_name() ) ); ?></h2>
					<div class="ss-bundle-row">
						<a class="ss-bundle-thumb" href="<?php echo esc_url( $bundle['partner']->get_permalink() ); ?>"><?php echo $bundle['partner']->get_image( 'woocommerce_gallery_thumbnail' ); // phpcs:ignore ?></a>
						<p>
							<?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?> + <?php echo wp_kses_post( wc_price( $bundle['partner']->get_price() ) ); ?> = <del><?php echo wp_kses_post( wc_price( $bundle['separate'] ) ); ?></del><br>
							<strong class="ss-bundle-price"><?php printf( esc_html__( 'Both for %s', 'second-sitting' ), wp_strip_all_tags( wc_price( $bundle['price'] ) ) ); ?></strong>
							<span class="ss-price-save"><?php printf( esc_html__( 'Save another %s', 'second-sitting' ), wp_strip_all_tags( wc_price( $bundle['saving'] ) ) ); ?></span>
						</p>
					</div>
					<form method="post" action="<?php echo esc_url( $product->get_permalink() ); ?>" data-ss-needs-postcode>
						<input type="hidden" name="add-to-cart" value="<?php echo (int) $product->get_id(); ?>">
						<input type="hidden" name="ss_bundle" value="<?php echo (int) $bundle['partner']->get_id(); ?>">
						<button type="submit" class="ss-btn ss-btn--outline ss-btn--block" disabled><?php esc_html_e( 'Reserve both', 'second-sitting' ); ?></button>
					</form>
				</section>
				<?php endif; ?>

				<?php if ( ! $sold ) : ?>
				<section class="ss-pc-panel">
					<h2><span class="ss-step">1</span><?php esc_html_e( 'Check delivery to you', 'second-sitting' ); ?></h2>
					<p><?php esc_html_e( 'We confirm your postcode is within 70km of Harrow before you reserve. Outside that? You can collect.', 'second-sitting' ); ?></p>
					<?php ss_postcode_form( 'product' ); ?>
				</section>

				<div class="ss-actions" data-ss-needs-postcode>
					<?php woocommerce_template_single_add_to_cart(); ?>
					<p class="ss-reserve-hint" data-ss-hint><?php esc_html_e( 'Check your postcode to unlock reserving. No payment until we confirm delivery.', 'second-sitting' ); ?></p>
					<a class="ss-btn ss-btn--outline ss-btn--block" href="<?php echo esc_url( $viewing ); ?>"><?php esc_html_e( 'Book a viewing in Harrow', 'second-sitting' ); ?></a>
				</div>
				<?php else : ?>
					<a class="ss-btn ss-btn--solid ss-btn--block" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'See sofas in stock', 'second-sitting' ); ?></a>
				<?php endif; ?>

				<div class="ss-details">
					<?php if ( $condition ) : ?>
					<div>
						<h2><?php esc_html_e( 'Condition', 'second-sitting' ); ?></h2>
						<?php echo wp_kses_post( wpautop( esc_html( $condition ) ) ); ?>
					</div>
					<?php endif; ?>

					<?php if ( $product->has_dimensions() ) : ?>
					<div>
						<h2><?php esc_html_e( 'Dimensions', 'second-sitting' ); ?></h2>
						<?php $unit = get_option( 'woocommerce_dimension_unit' ); ?>
						<dl class="ss-dims">
							<div><dt><?php esc_html_e( 'Width', 'second-sitting' ); ?></dt><dd><?php echo esc_html( $product->get_width() . ' ' . $unit ); ?></dd></div>
							<div><dt><?php esc_html_e( 'Depth', 'second-sitting' ); ?></dt><dd><?php echo esc_html( $product->get_length() . ' ' . $unit ); ?></dd></div>
							<div><dt><?php esc_html_e( 'Height', 'second-sitting' ); ?></dt><dd><?php echo esc_html( $product->get_height() . ' ' . $unit ); ?></dd></div>
						</dl>
					</div>
					<?php endif; ?>

					<?php if ( get_the_content() ) : ?>
					<div>
						<h2><?php esc_html_e( 'About this sofa', 'second-sitting' ); ?></h2>
						<?php the_content(); ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php
		$related = wc_get_related_products( $product->get_id(), 4 );
		if ( $related ) :
			?>
		<section class="ss-related">
			<h2 class="section-title section-title--left"><?php esc_html_e( 'You might', 'second-sitting' ); ?> <span><?php esc_html_e( 'also like', 'second-sitting' ); ?></span></h2>
			<ul class="products ss-grid">
				<?php
				foreach ( $related as $rid ) {
					$GLOBALS['post'] = get_post( $rid ); // phpcs:ignore
					setup_postdata( $GLOBALS['post'] );
					$GLOBALS['product'] = wc_get_product( $rid ); // phpcs:ignore
					wc_get_template_part( 'content', 'product' );
				}
				wp_reset_postdata();
				?>
			</ul>
		</section>
		<?php endif; ?>
	</div>
</main>

	<?php
endwhile;

get_template_part( 'parts/footer' );
