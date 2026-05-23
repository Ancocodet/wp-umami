<?php
namespace Ancozockt\Umami\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WooCommerceIntegration
 *
 * @since 0.9.0
 */
class WooCommerceIntegration {

	/**
	 * WooCommerceIntegration constructor.
	 *
	 * @since 0.9.0
	 */
	public function __construct() {
		// Check if WooCommerce is active.
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Add actions to track WooCommerce events.
		add_filter( 'woocommerce_pay_order_button_html', array( $this, 'filter_woocommerce_pay_order_button_html' ) );
	}

	/**
	 * Add event data attributes to the pay order button.
	 *
	 * @since 0.9.0
	 *
	 * @param string $button_html The button HTML.
	 *
	 * @return string The filtered button HTML.
	 */
	public function filter_woocommerce_pay_order_button_html( string $button_html ): string {
		$cart = WC()->cart;

		$total    = number_format( $cart->get_total( null ), 2, '.', '' );
		$discount = $cart->has_discount();

		// Add the event data attributes.
		return str_replace(
			'<button',
			"<button data-umami-event=\"pay-order\" data-umami-event-revenue=\"${total}\" data-umami-event-discount=\"${discount}\"",
			$button_html
		);
	}
}
