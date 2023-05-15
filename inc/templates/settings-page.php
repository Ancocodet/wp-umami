<?php
/**
 * The template for displaying the settings page.
 *
 * @since 0.1.0
 * @change 0.2.0 - Added option for ignoring admins.
 *
 * @package Integrate Umami
 */

?>
<form method="post" action="options.php" xmlns="http://www.w3.org/1999/html">
	<?php settings_fields( 'integration_umami' ); ?>
	<table class="form-table">
		<tr>
			<th scope="row">
				<label for="integration_umami_enabled"><?php esc_html_e( 'Enabled', 'integrate-umami' ); ?></label>
			</th>
			<td>
				<input type="checkbox" name="umami_options[enabled]" id="integration_umami_enabled"
					   value="1" <?php checked( $options['enabled'] ); ?> />
				<p class="description"><?php esc_html_e( 'Enable umami analytics', 'integrate-umami' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="integration_umami_script_url"><?php esc_html_e( 'Script Url', 'integrate-umami' ); ?></label>
			</th>
			<td>
				<input class="integrate-umami-url" type="url" name="umami_options[script_url]" id="integration_umami_script_url"
					   value="<?php echo esc_attr( $options['script_url'] ); ?>"/>
				<p class="description"><?php esc_html_e( 'The url to your umami tracking script', 'integrate-umami' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="integration_umami_host_url"><?php esc_html_e( 'Host Url', 'integrate-umami' ); ?></label>
			</th>
			<td>
				<input class="integrate-umami-url" type="url" name="umami_options[host_url]" id="integration_umami_host_url"
					   value="<?php echo esc_attr( $options['host_url'] ); ?>"/>
				<p class="description"><?php esc_html_e( 'The url to your umami instanace', 'integrate-umami' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="integration_umami_website_id"><?php esc_html_e( 'Website ID', 'integrate-umami' ); ?></label>
			</th>
			<td>
				<input class="integrate-umami-text"  type="text" name="umami_options[website_id]" id="integration_umami_website_id"
					   value="<?php echo esc_attr( $options['website_id'] ); ?>"/>
				<p class="description"><?php esc_html_e( 'The umami websiteId generated by your installation', 'integrate-umami' ); ?></p>
			</td>
		</tr>

		<tr>
			<th class="row">
				<label for="integration_umami_ignore_admins"><?php esc_html_e( 'Ignore Admins', 'integrate-umami' ); ?></label>
			</th>
			<td>
				<input type="checkbox" name="umami_options[ignore_admins]" id="integration_umami_ignore_admins"
					   value="1" <?php checked( $options['ignore_admins'] ); ?> />
				<p class="description"><?php esc_html_e( 'Disable tracking for admin users', 'integrate-umami' ); ?></p>
			</td>
		</tr>

		<tr>
			<th class="row">
				<label for="integration_umami_auto_track"><?php esc_html_e( 'Auto Track', 'integrate-umami' ); ?></label>
			</th>
			<td>
				<input type="checkbox" name="umami_options[auto_track]" id="integration_umami_auto_track"
					   value="1" <?php checked( $options['auto_track'] ); ?> />
				<p class="description"><?php esc_html_e( 'Enable auto tracking', 'integrate-umami' ); ?></p>
			</td>
		</tr>

		<tr>
			<th class="row">
				<label for="integration_umami_do_not_track"><?php esc_html_e( 'Do Not Track', 'integrate-umami' ); ?></label>
			</th>
			<td>
				<input type="checkbox" name="umami_options[do_not_track]" id="integration_umami_do_not_track"
					   value="1" <?php checked( $options['do_not_track'] ); ?> />
				<p class="description"><?php echo esc_html__( 'Respect visitor`s <b>Do Not Track</b> setting', 'integrate-umami' ); ?></p>
			</td>
		</tr>

		<tr>
			<th class="row">
				<label for="integration_umami_cache"><?php esc_html_e( 'Cache', 'integrate-umami' ); ?></label>
			</th>
			<td>
				<input type="checkbox" name="umami_options[cache]" id="integration_umami_cache"
					   value="1" <?php checked( $options['cache'] ); ?> />
				<p class="description"><?php esc_html_e( 'Cache data for better performance', 'integrate-umami' ); ?></p>
			</td>
		</tr>
	</table>

	<?php submit_button(); ?>
</form>
