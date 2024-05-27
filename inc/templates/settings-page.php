<?php
/**
 * The template for displaying the settings page.
 *
 * @since 0.1.0
 * @change 0.2.0 - Added option for ignoring admins.
 *
 * @global array $options Plugin Settings array.
 *
 * @package Integrate Umami
 */

?>
<form method="post" action="options.php" xmlns="http://www.w3.org/1999/html">
	<?php settings_fields( 'integrate_umami' ); ?>
	<table class="form-table">
		<tr>
			<th scope="row">
				<?php esc_html_e( 'Enabled', 'integrate-umami' ); ?>
			</th>
			<td>
				<label for="integrate_umami_enabled">
					<input type="checkbox" name="umami_options[enabled]" id="integrate_umami_enabled"
						value="1" <?php checked( $options['enabled'] ); ?> />
					<?php esc_html_e( 'Enable umami analytics', 'integrate-umami' ); ?>
				</label>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Script Url', 'integrate-umami' ); ?>
			</th>
			<td>
				<input class="integrate-umami-url" type="url" name="umami_options[script_url]" id="integrate_umami_script_url"
					value="<?php echo esc_attr( $options['script_url'] ); ?>"/>
				<p class="description"><?php esc_html_e( 'The url to your umami tracking script', 'integrate-umami' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row">
				<?php esc_html_e( 'Website ID', 'integrate-umami' ); ?>
			</th>
			<td>
				<input class="integrate-umami-text"  type="text" name="umami_options[website_id]" id="integrate_umami_website_id"
					value="<?php echo esc_attr( $options['website_id'] ); ?>"/>
				<p class="description"><?php esc_html_e( 'The umami websiteId generated by your installation', 'integrate-umami' ); ?></p>
			</td>
		</tr>
	</table>

	<div class="integrate-umami-collapsed">
		<input class="toggle" id="advanced-options" type="checkbox">
		<label class="toggle-label" for="advanced-options"><?php esc_html_e( 'Advanced Options', 'integrate-umami' ); ?></label>
		<div class="content">
			<table class="form-table">
				<tr>
					<th scope="row">
						<?php esc_html_e( 'Host Url', 'integrate-umami' ); ?>
					</th>
					<td>
						<input class="integrate-umami-url" type="url" name="umami_options[host_url]" id="integrate_umami_host_url"
							value="<?php echo esc_attr( $options['host_url'] ); ?>"/>
						<p class="description">
						<?php
						echo wp_kses(
							__( 'The URL of the Umami instance <b>if it is different from the URL to the script.</b>', 'integrate-umami' ),
							[
								'b' => [],
							]
						);
						?>
						</p>
					</td>
				</tr>

				<tr>
					<th class="row">
						<?php esc_html_e( 'Use Host Url', 'integrate-umami' ); ?>
					</th>
					<td>
						<label for="integrate_umami_use_host_url">
							<input type="checkbox" name="umami_options[use_host_url]" id="integrate_umami_use_host_url"
								value="1" <?php checked( $options['use_host_url'] ); ?> />
							<?php
							echo wp_kses(
								__( 'Use Host Url as data target. <a href="https://umami.is/docs/tracker-configuration">More information</a>', 'integrate-umami' ),
								[
									'a' => [
										'href' => [],
									],
								]
							);
							?>
						</label>
					</td>
				</tr>

				<tr>
					<th class="row">
						<?php esc_html_e( 'Ignore Admins', 'integrate-umami' ); ?>
					</th>
					<td>
						<label for="integrate_umami_ignore_admins">
							<input type="checkbox" name="umami_options[ignore_admins]" id="integrate_umami_ignore_admins"
								value="1" <?php checked( $options['ignore_admins'] ); ?> />
							<?php esc_html_e( 'Disable tracking for admin users', 'integrate-umami' ); ?>
						</label>
					</td>
				</tr>

				<tr>
					<th class="row">
						<?php esc_html_e( 'Auto Tracking', 'integrate-umami' ); ?>
					</th>
					<td>
						<label for="integrate_umami_auto_track">
							<input type="checkbox" name="umami_options[auto_track]" id="integrate_umami_auto_track"
								value="1" <?php checked( $options['auto_track'] ); ?> />
							<?php esc_html_e( 'Enable the automatic events and pageviews tracking', 'integrate-umami' ); ?>
							<p class="description">
								<?php
								echo wp_kses(
									__( '<b>Note</b>: You need to add your own <a href="https://umami.is/docs/tracker-functions">Tracker functions</a> when disabled.', 'integrate-umami' ),
									[
										'b' => [],
										'a' => [
											'href' => [],
										],
									]
								);
								?>
							</p>
						</label>
					</td>
				</tr>

				<tr>
					<th class="row">
						<?php esc_html_e( 'Track Comment Submit', 'integrate-umami' ); ?>
					</th>
					<td>
						<label for="integrate_umami_track_comments">
							<input type="checkbox" name="umami_options[track_comments]" id="integrate_umami_track_comments" value="1" <?php checked( $options['track_comments'] ); ?> />
							<?php
							echo wp_kses(
								__( 'Track Comment submits using <a href="https://umami.is/docs/track-events">events</a>', 'integrate-umami' ),
								[
									'a' => [
										'href' => [],
									],
								]
							);
							?>
						</label>
					</td>
				</tr>

				<tr>
					<th class="row">
						<?php esc_html_e( 'Do Not Track', 'integrate-umami' ); ?>
					</th>
					<td>
						<label for="integrate_umami_do_not_track">
							<input type="checkbox" name="umami_options[do_not_track]" id="integrate_umami_do_not_track"
								value="1" <?php checked( $options['do_not_track'] ); ?> />
							<?php
							echo wp_kses(
								__( 'Respect visitor`s <b>Do Not Track</b> setting', 'integrate-umami' ),
								[
									'b' => [],
								]
							);
							?>
						</label>
					</td>
				</tr>

				<tr>
					<th class="row">
						<?php esc_html_e( 'Cache', 'integrate-umami' ); ?>
					</th>
					<td>
						<label for="integrate_umami_cache">
							<input type="checkbox" name="umami_options[cache]" id="integrate_umami_cache"
								value="1" <?php checked( $options['cache'] ); ?> />
							<?php esc_html_e( 'Enable caching of tracking data for better performance', 'integrate-umami' ); ?>
							<p class="description">
								<?php
								echo wp_kses(
									__( '<b>Note</b>: This will use session storage, so you may need to inform your users. (Not Supported by Umami v2)', 'integrate-umami' ),
									[
										'b' => [],
									]
								);
								?>
							</p>
						</label>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<?php submit_button(); ?>
</form>
