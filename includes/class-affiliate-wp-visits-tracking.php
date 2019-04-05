<?php
/**
 * Affiliate_WP_Visits_Tracking class.
 *
 * @package Affiliate_WP_Visits_Tracking
 */

/**
 * The Affiliate_WP_Visits_Tracking class.
 *
 * @since  1.0
 * @uses   Affiliate_WP_Logging  Logs activity.
 */
class Affiliate_WP_Visits_Tracking {

	/**
	 * Refrerral variable.
	 *
	 * @access private
	 * @deprecated 2.0.2
	 *
	 * @var Affiliate_WP_Logging
	 */
	private $referral_variable = 'bsf';

	/**
	 * Logger instance.
	 *
	 * @access protected
	 * @deprecated 2.0.2
	 *
	 * @var Affiliate_WP_Logging
	 */
	protected $logs;

	/**
	 * Get things started
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->define_constants();
	}

	/**
	 * Define all required constants.
	 *
	 * @since 1.0
	 */
	private function define_constants() {

		$settings   = get_option( AWP_SETTINGS_GROUP );
		$public_key = '';
		$token      = '';
		if ( isset( $settings['public_key'] ) && isset( $settings['token'] ) ) {
			$public_key = $settings['public_key'];
			$token      = $settings['token'];
		}

		define( 'AFFILIATE_WP_REST_UN', $public_key );
		define( 'AFFILIATE_WP_REST_PWD', $token );

	}

	/**
	 * Get options
	 *
	 * @param array $option options.
	 * @since 1.0
	 */
	private function get_option( $option = '' ) {
		$options = get_option( 'affiliatewp_external_referral_links' );

		if ( ! isset( $option ) ) {
			return;
		}

		return $options[ $option ];
	}


	/**
	 * Record referral visit via ajax
	 *
	 * @since 1.0
	 */
	public function track_visit() {

		$affiliate_id        = isset( $_GET[ $this->referral_variable ] ) ? absint( $_GET[ $this->referral_variable ] ) : 0;
		$cookie_affiliate_id = isset( $_COOKIE['affwp_affiliate_id'] ) ? absint( $_COOKIE['affwp_affiliate_id'] ) : 0;

		if ( $affiliate_id !== $cookie_affiliate_id ) {
			$campaign     = isset( $_GET['campaign'] ) ? sanitize_text_field( $_GET['campaign'] ) : '';
			$landing_page = $this->get_option( 'url' );
			$store_url    = $landing_page . '/wp-json/affwp/v1/visits';

			$pload = array(
				'method'      => 'POST',
				'timeout'     => 30,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(
					'Authorization' => 'Basic ' . base64_encode( AFFILIATE_WP_REST_UN . ':' . AFFILIATE_WP_REST_PWD ),
				),
				'body'        => '',
				'cookies'     => array(),
			);

			$store_url = add_query_arg(
				array(
					'affiliate_id' => $affiliate_id,
					'ip'           => $this->get_ip(),
					'url'          => esc_url( $landing_page ),
					'campaign'     => $campaign,
					'referrer'     => esc_url( home_url() . $_SERVER['REQUEST_URI'] ),
				),
				$store_url
			);

			$response = wp_remote_post( $store_url, $pload );
			$code     = wp_remote_retrieve_response_code( $response );

			if ( ! is_wp_error( $response ) && ( 201 == $code || 200 == $code ) ) {
				$cookie_time = '+' . $this->get_option( 'cookie_expiration' ) . ' day';
				setcookie( 'affwp_affiliate_id', $affiliate_id, strtotime( $cookie_time ), '/' );
				setcookie( 'affwp_erl_id', $affiliate_id, strtotime( $cookie_time ), '/' );
			}
		}

	}

	/**
	 * Get the visitor's IP address
	 *
	 * @since 1.0
	 */
	public function get_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// check ip from share internet.
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// to check ip is pass from proxy.
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

}
