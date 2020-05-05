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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="wpbody" role="main">
	<div id="wpbody-content">
		<div class="wrap">
			<h1 class="wp-heading-inline">
				<span class="dashicons dashicons-yes-alt"></span>
				<?php esc_html_e( 'RGOU WP Media', 'rgou-wp-media' ); ?>
			</h1>
			<hr />
			<h2><?php esc_html_e( 'See links from your homepage.', 'rgou-wp-media' ); ?></h2>

			<form action="<?php menu_page_url( 'rgou-wp-admin' ); ?>" method="post">

				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Run now', 'rgou-wp-media' ); ?>">
				</p>
				<?php wp_nonce_field( 'wp_rgou_wp__option_page_action' ); ?>
			</form>

			<h2><?php esc_html_e( 'Current values.', 'rgou-wp-media' ); ?></h2>

			<ul>
				<?php foreach ( $values['links'] as $rgou_wp_media_link ) : ?>
					<li>
						<a href="<?php echo esc_attr( $rgou_wp_media_link ); ?>">
						<?php echo esc_html( $rgou_wp_media_link ); ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>

		</div><!-- wrap -->
	</div><!-- wpbody -->
</div><!-- wpbody-content -->

<script>
	console.log(<?php echo wp_json_encode( $values ); ?>);
</script>
