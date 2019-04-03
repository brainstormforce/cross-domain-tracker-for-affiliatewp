<?php
/**
 * Affiliate_WP_Visits_Tracking class.
 *
 * @package Affiliate_WP_Visits_Tracking
 */

define( 'AFFILIATE_WP_REST_UN', 'c2f2cd32d9ec789b22df2e671ff54e6d' );
define( 'AFFILIATE_WP_REST_PWD', '5c613e7a776658b63050cb9f062fa5ca' );

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
		if ( ! isset( $_COOKIE['affwp_visit_id'] ) ) {
			$referrer     = ! empty( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( $_SERVER['HTTP_REFERER'] ) : '';
			$campaign     = isset( $_GET['campaign'] ) ? sanitize_text_field( $_GET['campaign'] ) : '';
			$affiliate_id = isset( $_GET[ $this->referral_variable ] ) ? absint( $_GET[ $this->referral_variable ] ) : 0;
			$store_url    = $this->get_option( 'url' ) . '/wp-json/affwp/v1/visits';

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
					'url'          => set_url_scheme( home_url( '/' ) . $_SERVER['REQUEST_URI'] ),
					'campaign'     => $campaign,
					'referrer'     => $referrer,
				),
				$store_url
			);

			$response = wp_remote_post( $store_url, $pload );

			if ( ! is_wp_error( $response ) ) {
				$body          = wp_remote_retrieve_body( $response );
				$response_json = json_decode( $body );
				$cookie_time   = '+' . $this->get_option( 'cookie_expiration' ) . ' day';
				setcookie( 'affwp_visit_id', $response_json->visit_id, strtotime( $cookie_time ) );
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
