<?php
/**
 * Site footer. Contact details are edited under Appearance > Customize > Second Sitting.
 */
defined( 'ABSPATH' ) || exit;

$address = get_theme_mod( 'ss_address', '[Collection address], Harrow' );
$email   = get_theme_mod( 'ss_email', '' );
$phone   = get_theme_mod( 'ss_phone', '' );
$fb      = get_theme_mod( 'ss_facebook', '' );
$ig      = get_theme_mod( 'ss_instagram', '' );
?>
<footer class="ss-footer">
	<div class="wrap ss-foot">
		<div>
			<a class="ss-logo ss-logo--dark" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="ss-logo-mark">SS</span>
				<span class="ss-logo-name">SECOND SITTING</span>
			</a>
			<p class="ss-foot-about"><?php esc_html_e( 'Giving sofas another chance. Premium pre-loved sofas from Harrow, delivered across London and the Home Counties.', 'second-sitting' ); ?></p>
			<div class="ss-social">
				<?php if ( $ig ) : ?>
				<a href="<?php echo esc_url( $ig ); ?>" aria-label="Instagram"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/></svg></a>
				<?php endif; ?>
				<?php if ( $fb ) : ?>
				<a href="<?php echo esc_url( $fb ); ?>" aria-label="Facebook"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 3h-3a4 4 0 00-4 4v3H5v4h3v7h4v-7h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
				<?php endif; ?>
			</div>
			<p class="ss-copy">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Second Sitting. <?php esc_html_e( 'All rights reserved.', 'second-sitting' ); ?></p>
		</div>

		<nav aria-label="<?php esc_attr_e( 'Footer', 'second-sitting' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'ss-footer',
				'container'      => false,
				'menu_class'     => 'ss-foot-links',
				'fallback_cb'    => 'ss_fallback_menu',
				'depth'          => 1,
			) );
			?>
		</nav>

		<nav aria-label="<?php esc_attr_e( 'Legal', 'second-sitting' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'ss-legal',
				'container'      => false,
				'menu_class'     => 'ss-foot-links',
				'fallback_cb'    => function () {
					echo '<ul class="ss-foot-links">';
					if ( get_privacy_policy_url() ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_privacy_policy_url() ), esc_html__( 'Privacy policy', 'second-sitting' ) );
					}
					if ( function_exists( 'wc_terms_and_conditions_page_id' ) && wc_terms_and_conditions_page_id() ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( wc_terms_and_conditions_page_id() ) ), esc_html__( 'Terms of sale', 'second-sitting' ) );
					}
					echo '</ul>';
				},
				'depth'          => 1,
			) );
			?>
		</nav>

		<ul class="ss-contact">
			<li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6-7-11a7 7 0 0114 0c0 5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg><?php echo esc_html( $address ); ?></li>
			<?php if ( $email ) : ?>
			<li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
			<?php endif; ?>
			<?php if ( $phone ) : ?>
			<li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/></svg><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
			<?php endif; ?>
		</ul>
	</div>
</footer>

</div><!-- .ss -->
<?php wp_footer(); ?>
</body>
</html>
