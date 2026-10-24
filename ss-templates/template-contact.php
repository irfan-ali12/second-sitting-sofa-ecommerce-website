<?php
/**
 * Template Name: Second Sitting Contact / Viewings
 *
 * Book a viewing or ask a question. Created automatically as the "Contact & Viewings" page.
 * Any text typed into the page editor appears above the form.
 */
defined( 'ABSPATH' ) || exit;

$address = get_theme_mod( 'ss_address', '[Collection address], Harrow' );
$phone   = get_theme_mod( 'ss_phone', '' );
$email   = get_theme_mod( 'ss_email', '' );
$hours   = get_theme_mod( 'ss_hours', 'Mon–Sat 9am–6pm, Sun by appointment' );
$map_q   = get_theme_mod( 'ss_map_query', 'Harrow, London' );
$wa      = $phone ? 'https://wa.me/' . preg_replace( '/^0/', '44', preg_replace( '/[^0-9]/', '', $phone ) ) : '';

$selected = isset( $_GET['sofa'] ) ? absint( $_GET['sofa'] ) : 0; // phpcs:ignore
$sent     = isset( $_GET['sent'] ); // phpcs:ignore
$error    = isset( $_GET['ss_error'] ) ? sanitize_key( $_GET['ss_error'] ) : ''; // phpcs:ignore
$saved_pc = ss_get_saved_postcode_result();

$sofas = function_exists( 'wc_get_products' ) ? wc_get_products( array( 'limit' => 50, 'status' => 'publish', 'stock_status' => 'instock', 'orderby' => 'date', 'order' => 'DESC' ) ) : array();
$selected_product = $selected && function_exists( 'wc_get_product' ) ? wc_get_product( $selected ) : null;

get_template_part( 'parts/header', null, array( 'variant' => 'inner' ) );
?>
<main id="ss-main" class="ss-page ss-contact-page">
	<section class="ss-page-hero">
		<div class="wrap">
			<h1><?php esc_html_e( 'Come and sit on it', 'second-sitting' ); ?></h1>
			<p><?php esc_html_e( 'Viewings are always welcome at our Harrow unit. Pick a day and we\'ll confirm the time, usually the same day.', 'second-sitting' ); ?></p>
		</div>
	</section>

	<div class="wrap ss-contact-grid">
		<section id="viewing" class="ss-contact-form-wrap" aria-labelledby="ss-form-title">
			<?php
			if ( is_page() ) {
				$intro = trim( (string) get_post_field( 'post_content', get_queried_object_id() ) );
				if ( $intro ) {
					echo '<div class="ss-contact-intro">' . apply_filters( 'the_content', $intro ) . '</div>'; // phpcs:ignore
				}
			}
			?>

			<?php if ( $sent ) : ?>
				<div class="ss-pc-result is-inside ss-form-done" role="status">
					<span class="ss-pc-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12l5 5 9-10"/></svg></span>
					<div><strong><?php esc_html_e( 'Thanks, we\'ve got your request', 'second-sitting' ); ?></strong><span><?php esc_html_e( 'We\'ll call or message you shortly to confirm.', 'second-sitting' ); ?></span></div>
				</div>
			<?php endif; ?>

			<?php if ( $error ) : ?>
				<div class="ss-pc-result is-error" role="alert">
					<?php echo 'missing' === $error ? esc_html__( 'Please add your name and either an email address or a phone number.', 'second-sitting' ) : esc_html__( 'The form timed out. Please try again.', 'second-sitting' ); ?>
				</div>
			<?php endif; ?>

			<h2 id="ss-form-title"><?php esc_html_e( 'Book a viewing', 'second-sitting' ); ?></h2>

			<?php if ( $selected_product ) : ?>
				<a class="ss-contact-sofa" href="<?php echo esc_url( $selected_product->get_permalink() ); ?>">
					<?php echo $selected_product->get_image( 'woocommerce_gallery_thumbnail' ); // phpcs:ignore ?>
					<span><small><?php esc_html_e( 'Viewing', 'second-sitting' ); ?></small><?php echo esc_html( $selected_product->get_name() ); ?></span>
				</a>
			<?php endif; ?>

			<form class="ss-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ss_enquiry">
				<?php wp_nonce_field( 'ss_enquiry', 'ss_enquiry_nonce' ); ?>
				<div class="ss-hp" aria-hidden="true"><label>Website <input type="text" name="ss_website" tabindex="-1" autocomplete="off"></label></div>

				<fieldset class="ss-seg">
					<legend class="screen-reader-text"><?php esc_html_e( 'What can we help with?', 'second-sitting' ); ?></legend>
					<label><input type="radio" name="ss_type" value="viewing" checked> <span><?php esc_html_e( 'Book a viewing', 'second-sitting' ); ?></span></label>
					<label><input type="radio" name="ss_type" value="question"> <span><?php esc_html_e( 'Ask a question', 'second-sitting' ); ?></span></label>
				</fieldset>

				<div class="ss-form-row">
					<label><span><?php esc_html_e( 'Your name', 'second-sitting' ); ?> *</span><input type="text" name="ss_name" required autocomplete="name"></label>
					<label><span><?php esc_html_e( 'Phone / WhatsApp', 'second-sitting' ); ?></span><input type="tel" name="ss_phone" autocomplete="tel"></label>
				</div>
				<div class="ss-form-row">
					<label><span><?php esc_html_e( 'Email', 'second-sitting' ); ?></span><input type="email" name="ss_email" autocomplete="email"></label>
					<label><span><?php esc_html_e( 'Your postcode', 'second-sitting' ); ?></span><input type="text" name="ss_postcode" autocomplete="postal-code" value="<?php echo $saved_pc ? esc_attr( $saved_pc['postcode'] ) : ''; ?>"></label>
				</div>

				<?php if ( $sofas ) : ?>
				<label><span><?php esc_html_e( 'Which sofa?', 'second-sitting' ); ?></span>
					<select name="ss_sofa">
						<option value="0"><?php esc_html_e( 'Just browsing / several', 'second-sitting' ); ?></option>
						<?php foreach ( $sofas as $s ) : ?>
							<option value="<?php echo (int) $s->get_id(); ?>" <?php selected( $selected, $s->get_id() ); ?>><?php echo esc_html( $s->get_name() . ' – ' . wp_strip_all_tags( wc_price( $s->get_price() ) ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<?php endif; ?>

				<div class="ss-form-row" data-ss-viewing-only>
					<label><span><?php esc_html_e( 'Preferred day', 'second-sitting' ); ?></span><input type="date" name="ss_date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>"></label>
					<label><span><?php esc_html_e( 'Time', 'second-sitting' ); ?></span>
						<select name="ss_time">
							<option value=""><?php esc_html_e( 'Any time', 'second-sitting' ); ?></option>
							<option><?php esc_html_e( 'Morning', 'second-sitting' ); ?></option>
							<option><?php esc_html_e( 'Afternoon', 'second-sitting' ); ?></option>
							<option><?php esc_html_e( 'Evening', 'second-sitting' ); ?></option>
						</select>
					</label>
				</div>

				<label><span><?php esc_html_e( 'Message', 'second-sitting' ); ?></span><textarea name="ss_message" rows="4" placeholder="<?php esc_attr_e( 'Anything we should know? e.g. access, stairs, measurements', 'second-sitting' ); ?>"></textarea></label>

				<button class="ss-btn ss-btn--solid ss-btn--block" type="submit"><?php esc_html_e( 'Send request', 'second-sitting' ); ?></button>
				<p class="ss-form-note"><?php esc_html_e( 'We only use your details to arrange your viewing or answer your question.', 'second-sitting' ); ?></p>
			</form>
		</section>

		<aside class="ss-contact-info" aria-label="<?php esc_attr_e( 'Visit and contact details', 'second-sitting' ); ?>">
			<div class="ss-info-card">
				<h2><?php esc_html_e( 'Visit us', 'second-sitting' ); ?></h2>
				<ul class="ss-contact">
					<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6-7-11a7 7 0 0114 0c0 5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg><?php echo esc_html( $address ); ?></li>
					<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><?php echo esc_html( $hours ); ?></li>
					<?php if ( $phone ) : ?>
					<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/></svg><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $email ) : ?>
					<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<?php endif; ?>
				</ul>
				<?php if ( $wa ) : ?>
					<a class="ss-btn ss-btn--solid ss-btn--block" href="<?php echo esc_url( $wa ); ?>"><?php esc_html_e( 'Message us on WhatsApp', 'second-sitting' ); ?></a>
				<?php endif; ?>
			</div>

			<div class="ss-map">
				<iframe title="<?php esc_attr_e( 'Map of our Harrow location', 'second-sitting' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
					src="https://maps.google.com/maps?q=<?php echo rawurlencode( $map_q ); ?>&amp;z=13&amp;output=embed"></iframe>
			</div>

			<div class="ss-info-card ss-info-card--tint">
				<h2><?php esc_html_e( 'Delivery area', 'second-sitting' ); ?></h2>
				<p><?php esc_html_e( 'Same or next-day delivery within 70km of Harrow, covering London and much of the Home Counties. Further away? You\'re welcome to collect.', 'second-sitting' ); ?></p>
				<?php ss_postcode_form( 'product' ); ?>
			</div>
		</aside>
	</div>
</main>
<?php
get_template_part( 'parts/footer' );
