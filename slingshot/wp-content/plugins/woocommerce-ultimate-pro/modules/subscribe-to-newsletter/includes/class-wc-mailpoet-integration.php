<?php

/**
 * WC_Mailpoet_Integration class.
 *
 * https://support.mailpoet.com/knowledgebase/plugin-form-integrate/
 */
class WC_Mailpoet_Integration {

	private $list;

	/**
	 * Constructor
	 */
	public function __construct( $list = false ) {
		$this->list = $list;
	}

	/**
	 * Checks to see if the Mailpoet plugin is installed, so we don't fatal sites
	 */
	public function is_plugin_installed() {
		if ( ! class_exists( 'WYSIJA' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * has_list function.
	 *
	 * @access public
	 * @return void
	 */
	public function has_list() {
		if ( $this->list ) {
			return true;
		}
	}

	/**
	 * get_lists function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_lists() {
		if ( ! $this->is_plugin_installed() ) {
			return array();
		}

		$lists = array();
		$model_list = WYSIJA::get( 'list','model' );
		$mailpoet_lists = $model_list->get( array( 'name','list_id' ), array( 'is_enabled' => 1 ) );

		if ( ! empty ( $mailpoet_lists ) && is_array( $mailpoet_lists ) ) {
			foreach ( $mailpoet_lists as $list ) {
				$lists[ $list['list_id'] ] = $list['name'];
			}
		}

		return $lists;
	}

	/**
	 * show_stats function.
	 *
	 * @access public
	 * @return void
	 */
	public function show_stats() {
		if ( ! $this->is_plugin_installed() ) {
			return;
		}

		$model_user = WYSIJA::get( 'user', 'model' );
		$select = array( 'COUNT(`user_id`) AS users' , 'status' );

		// Find total subscribers and subscribes
		$count_by_status = $model_user->get_subscribers( $select , array() , 'status' );
		$counts['subscribed'] = 0;
		$counts['unsubscribed'] = 0;
		foreach ( $count_by_status as $count ) {
			if ( '-1' == $count['status'] ) {
				$counts['unsubscribed'] = $count['users'];
				continue;
			}
			else if ( '1' == $count['status'] ) {
				$counts['subscribed'] = $count['users'];
				continue;
			}
		}

		$stats  = '<ul class="woocommerce_stats" style="word-wrap:break-word;">';
		$stats .= '<li><strong style="font-size:3em;">' . esc_html( $counts['subscribed'] ) . '</strong> ' . esc_html__( 'Total subscribers', 'ultimatewoo-pro' ) . '</li>';
		$stats .= '<li><strong style="font-size:3em;">' . esc_html( $counts['unsubscribed'] ) . '</strong> ' . esc_html__( 'Unsubscribes', 'ultimatewoo-pro' ) . '</li>';
		$stats .= '</ul>';

		echo $stats;
	}

	/**
	 * subscribe function.
	 *
	 * @access public
	 * @param mixed $first_name
	 * @param mixed $last_name
	 * @param mixed $email
	 * @param string $listid (default: false)
	 * @return void
	 */
	public function subscribe( $first_name, $last_name, $email, $listid = false ) {
		if ( ! $this->is_plugin_installed() ) {
			return;
		}

		if ( false === $listid ) {
			$listid = $this->list;
		}

		$user_data = array(
			'email' => $email,
			'firstname' => $first_name,
			'lastname' => $last_name
		);

		$subscriber = array(
			'user' => $user_data,
			'user_list' => array(
				'list_ids' => array( $listid )
			)
		);

		$user_helper = WYSIJA::get( 'user','helper' );
		$add_subscriber = $user_helper->addSubscriber( $subscriber );

		if ( false === $add_subscriber ) {
			$response = "";
			$messages = $user_helper->getMsgs();

			if ( is_array( $messages ) && is_array( $messages['error'] ) ) {
				foreach( $messages['error'] as $error ) {
					$response .= $error . "\n";
				}
			} else {
				$response = esc_html__( 'No error messages were returned by MailPoet.', 'ultimatewoo-pro' );
			}

			wp_mail(
				get_option( 'admin_email' ),
				esc_html__( 'Email subscription failed (MailPoet)', 'ultimatewoo-pro' ),
				esc_html__( 'Error:', 'ultimatewoo-pro' ) . "\n\n" . $response
			);

			return;
		}

		do_action( 'wc_subscribed_to_newsletter', $email );
	}

}
