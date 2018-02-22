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
 * @package   WC-Braintree/Gateway/Payment-Form/PayPal
 * @author    SkyVerge
 * @copyright Copyright: (c) 2011-2016, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Braintree PayPal Payment Form
 *
 * @since 3.0.0
 */
class WC_Braintree_PayPal_Payment_Form extends WC_Braintree_Payment_Form {


	/**
	 * Return the JS params passed to the the payment form handler script
	 *
	 * @since 3.0.0
	 * @see WC_Braintree_Payment_Form::get_payment_form_handler_js_params()
	 * @return array
	 */
	protected function get_payment_form_handler_js_params() {

		return array( 'must_login_message' => __( 'Please click the blue "PayPal" button below to log into your PayPal account before placing your order.', 'ultimatewoo-pro' ) );
	}


	/**
	 * Get the PayPal checkout locale based on the WordPress locale
	 *
	 * @link http://wpcentral.io/internationalization/
	 * @link https://developers.braintreepayments.com/guides/paypal/vault/javascript/v2#country-and-language-support
	 *
	 * @since 3.0.0
	 * @return string locale
	 */
	protected function get_safe_locale() {

		$locale = strtolower( get_locale() );

		$safe_locales = array(
				'en_au',
				'de_at',
				'en_be',
				'en_ca',
				'da_dk',
				'en_us',
				'fr_fr',
				'de_de',
				'en_gb',
				'zh_hk',
				'it_it',
				'nl_nl',
				'no_no',
				'pl_pl',
				'es_es',
				'sv_se',
				'en_ch',
				'tr_tr',
				'es_xc',
				'fr_ca',
				'ru_ru',
				'en_nz',
				'pt_pt',
		);

		if ( ! in_array( $locale, $safe_locales ) ) {
			$locale = 'en_us';
		}

		/**
		 * Braintree PayPal Locale Filter.
		 *
		 * Allow actors to filter the locale used for the Braintree SDK
		 *
		 * @since 3.0.0
		 * @param string $lang The button locale.
		 * @return string
		 */
		return apply_filters( 'wc_braintree_paypal_locale', $locale );
	}


	/**
	 * Render the PayPal container div, which is replaced by the PayPal button
	 * when the frontend JS executes. This also renders 3 hidden inputs:
	 *
	 * 1) wc_braintree_paypal_amount - order total
	 * 2) wc_braintree_paypal_currency - active store currency
	 * 3) wc_braintree_paypal_locale - site locale
	 *
	 * Note these are rendered as hidden inputs and not passed to the script constructor
	 * because these will be refreshed and re-rendered when the checkout updates,
	 * which is important for the accuracy of things like the order total.
	 *
	 * Also note that the order total is used for rendering info inside the PayPal
	 * modal and _not_ for actual processing for the transaction, so there's no
	 * security concerns here.
	 *
	 * @since 3.0.0
	 */
	public function render_payment_fields() {

		parent::render_payment_fields();

		?>
			<div id="wc_braintree_paypal_container"></div>
			<input type="hidden" name="wc_braintree_paypal_amount" value="<?php echo esc_attr( SV_WC_Helper::number_format( WC()->cart->total, 2 ) ); ?>" />
			<input type="hidden" name="wc_braintree_paypal_currency" value="<?php echo esc_attr( get_woocommerce_currency() ); ?>" />
			<input type="hidden" name="wc_braintree_paypal_locale" value="<?php echo esc_attr( $this->get_safe_locale() ); ?>" />
		<?php
	}


}
