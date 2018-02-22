<?php
/**
 * WooCommerce Braintree Gateway
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Braintree Gateway to newer
 * versions in the future. If you wish to customize WooCommerce Braintree Gateway for your
 * needs please refer to http://docs.woothemes.com/document/braintree/
 *
 * @package   WC-Braintree/Gateway/Payment-Method-Handler
 * @author    SkyVerge
 * @copyright Copyright: (c) 2011-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree Payment Method Handler Class
 *
 * Extends the framework payment tokens handler class to provide Braintree-specific
 * functionality
 *
 * @since 3.2.0
 */
class WC_Braintree_Payment_Method_Handler extends SV_WC_Payment_Gateway_Payment_Tokens_Handler {


	/**
	 * Return a custom payment token class instance
	 *
	 * @since 3.0.0
	 * @see SV_WC_Payment_Gateway_Payment_Tokens_Handler::build_token()
	 * @param string $token_id token ID
	 * @param array $data token data
	 * @return \WC_Braintree_Payment_Method
	 */
	public function build_token( $token_id, $data ) {

		return new WC_Braintree_Payment_Method( $token_id, $data );
	}


	/**
	 * When retrieving payment methods via the Braintree API, it returns both
	 * credit/debit card *and* PayPal methods from a single call. Overriding
	 * the core framework update method ensures that PayPal accounts are not saved to
	 * the credit card token meta entry, and vice versa.
	 *
	 * @since 3.0.0
	 * @param int $user_id WP user ID
	 * @param array $tokens array of tokens
	 * @param string $environment_id optional environment id, defaults to plugin current environment
	 * @return string updated user meta id
	 */
	public function update_tokens( $user_id, $tokens, $environment_id = null ) {

		foreach ( $tokens as $token_id => $token ) {

			if ( ( $this->get_gateway()->is_credit_card_gateway() && ! $token->is_credit_card() ) || ( $this->get_gateway()->is_paypal_gateway() && ! $token->is_paypal_account() ) ) {
				unset( $tokens[ $token_id ] );
			}
		}

		return parent::update_tokens( $user_id, $tokens, $environment_id );
	}


}
