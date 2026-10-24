<?php
/**
 * Shared layout for Cart, Checkout and My Account.
 *
 * Renders the classic WooCommerce shortcodes so the postcode check and the
 * branded styling apply even if the page itself holds the Cart/Checkout blocks.
 *
 * @var array $args { shortcode, title, intro, steps }
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header', null, array( 'variant' => 'inner' ) );

$step  = isset( $args['step'] ) ? (int) $args['step'] : 0;
$steps = array(
	1 => __( 'Check postcode', 'second-sitting' ),
	2 => __( 'Reserve', 'second-sitting' ),
	3 => __( 'We confirm & deliver', 'second-sitting' ),
);
?>
<main id="ss-main" class="ss-page ss-woo ss-woo--<?php echo esc_attr( $args['slug'] ); ?>">
	<section class="ss-page-hero ss-page-hero--compact">
		<div class="wrap">
			<h1><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( ! empty( $args['intro'] ) ) : ?><p><?php echo esc_html( $args['intro'] ); ?></p><?php endif; ?>
			<?php if ( $step ) : ?>
			<ol class="ss-progress">
				<?php foreach ( $steps as $n => $label ) : ?>
					<li class="<?php echo $n < $step ? 'is-done' : ( $n === $step ? 'is-current' : '' ); ?>"<?php echo $n === $step ? ' aria-current="step"' : ''; ?>><span><?php echo (int) $n; ?></span><?php echo esc_html( $label ); ?></li>
				<?php endforeach; ?>
			</ol>
			<?php endif; ?>
		</div>
	</section>
	<div class="wrap ss-woo-body">
		<?php echo do_shortcode( '[' . $args['shortcode'] . ']' ); // phpcs:ignore ?>
	</div>
</main>
<?php
get_template_part( 'parts/footer' );
