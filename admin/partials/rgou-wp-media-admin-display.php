<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://me.rgou.net
 * @since      1.0.0
 *
 * @package    Rgou_Wp_Media
 * @subpackage Rgou_Wp_Media/admin/partials
 */

?>

<div id="wpbody" role="main">
	<div id="wpbody-content">
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<span class="dashicons dashicons-yes-alt"></span>
				<?php esc_html_e( 'RGOU Sitemap', 'rgou-wp-media' ); ?>
			</h1>
			<hr />
			<h2><?php esc_html_e( 'Generate a sitemap for your homepage.', 'rgou-wp-media' ); ?></h2>

			<form action="<?php menu_page_url( 'rgou-wp-admin' ); ?>" method="post">

				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Run now', 'rgou-wp-media' ); ?>">
					<?php if ( isset( $values ) && array_key_exists( 'timestamp', $values ) && $values['timestamp'] ) : ?>
						<input type="submit" name="disable" id="submit" class="button button-secondary" value="<?php esc_html_e( 'Disable', 'rgou-wp-media' ); ?>">
					<?php endif ?>
				</p>
				<?php wp_nonce_field( 'rgou_wp_media_option_page_action' ); ?>
			</form>

			<?php if ( isset( $values ) && array_key_exists( 'timestamp', $values ) && $values['timestamp'] ) : ?>
				<p>
					<a href="<?php echo esc_attr( get_site_url() . '/sitemap.html' ); ?>" target="_blank"><?php esc_html_e( 'Sitemap', 'rgou-wp-media' ); ?></a>
					- <a href="<?php echo esc_attr( get_site_url() . '/index.html' ); ?>" target="_blank"><?php esc_html_e( 'Static homepage', 'rgou-wp-media' ); ?></a>
				</p>

				<?php if ( isset( $values ) && array_key_exists( 'links', $values ) && $values['links'] && is_array( $values['links'] ) ) : ?>
					<h2><?php esc_html_e( 'Current values.', 'rgou-wp-media' ); ?></h2>

					<ul>
						<?php foreach ( $values['links'] as $rgou_wp_media_link ) : ?>
							<li>
								<a href="<?php echo esc_attr( $rgou_wp_media_link['href'] ); ?>">
								<?php echo esc_html( $rgou_wp_media_link['label'] ); ?>
							</a>
						</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
				<h2><?php esc_html_e( 'No links so far.', 'rgou-wp-media' ); ?></h2>
				<?php endif ?>
			<?php endif ?>
		</div><!-- wrap -->
	</div><!-- wpbody -->
</div><!-- wpbody-content -->
