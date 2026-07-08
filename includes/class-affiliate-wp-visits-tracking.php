<?php
/**
 * Affiliate_WP_Visits_Tracking class.
 *
 * @package Affiliate_WP_Visits_Tracking
 */

/**
 * The Affiliate_WP_Visits_Tracking class.
 *
 * @since 1.0
 * @uses  Affiliate_WP_Logging  Logs activity.
 */
class Affiliate_WP_Visits_Tracking {


	/**
	 * Refrerral variable.
	 *
	 * @access     private
	 * @deprecated 2.0.2
	 *
	 * @var Affiliate_WP_Logging
	 */
	private $referral_variable = 'bsf';

	/**
	 * Logger instance.
	 *
	 * @access     protected
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

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

		/*
		 * Record the visit over AJAX so the tracking cookies are set on an
		 * uncached request. When a full-page cache / CDN serves the landing
		 * page, it strips (or never generates) the server-side Set-Cookie
		 * headers, so cookies set during a normal page render can be lost.
		 * admin-ajax.php responses are never cached, so this path reliably
		 * sets the cookies regardless of front-end caching.
		 *
		 * Registered in the constructor because the AJAX request runs in the
		 * admin context, where the front-end tracking hooks do not fire.
		 */
		$settings = get_option( CDTAWP_SETTINGS_GROUP );
		if ( isset( $settings['cdtawp_plugin_type'] ) && CDTAWP_PLUGIN_CHILD === $settings['cdtawp_plugin_type'] ) {
			add_action( 'wp_ajax_cdtawp_track_visit', array( $this, 'ajax_track_visit' ) );
			add_action( 'wp_ajax_nopriv_cdtawp_track_visit', array( $this, 'ajax_track_visit' ) );
		}
	}

	/**
	 * Load JS files
	 *
	 * @since 1.0
	 */
	public function load_scripts() {

		// return if no URL is set.
		if ( ! $this->get_option( 'cdtawp_store_url' ) ) {
			return;
		}

		wp_enqueue_script( 'awp-track-visit', Affiliate_WP_Track_External_Visits::$plugin_url . 'assets/js/tracking-visits.js', array( 'jquery' ), CDTAWP_VERSION, true );

		wp_localize_script(
			'awp-track-visit',
			'awp_track_visit_var',
			array(

				'referral_variable' => $this->get_option( 'cdtawp_referral_variable' ),
				'url'               => $this->get_option( 'cdtawp_store_url' ),
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			)
		);

	}

	/**
	 * Define all required constants.
	 *
	 * @since 1.0
	 */
	private function define_constants() {

		$settings   = get_option( CDTAWP_SETTINGS_GROUP );
		$public_key = '';
		$token      = '';
		if ( isset( $settings['cdtawp_public_key'] ) && isset( $settings['cdtawp_token'] ) ) {
			$public_key = $settings['cdtawp_public_key'];
			$token      = $settings['cdtawp_token'];
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
		$options = get_option( CDTAWP_SETTINGS_GROUP );

		if ( ! isset( $option ) ) {
			return;
		}

		return $options[ $option ];
	}

	/**
	 * Catch referral visit via ajax
	 */
	public function track_visit_receiver() {

		$affwp_settings    = get_option( 'affwp_settings' );
		$referral          = isset( $affwp_settings['referral_var'] ) ? $affwp_settings['referral_var'] : '';
		$cookie_expiration = isset( $affwp_settings['cookie_exp'] ) ? $affwp_settings['cookie_exp'] : 0;

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$affiliate_id         = isset( $_GET[ $referral ] ) ? $_GET[ $referral ] : 0;
		$affiliate_visited_id = isset( $_GET['visit'] ) ? $_GET['visit'] : 0;
		$affwp_campaign       = isset( $_GET['campaign'] ) ? $_GET['campaign'] : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( $affiliate_id && $affiliate_visited_id ) {
			$visit_details = $this->get_visit( $affiliate_visited_id );
			if ( isset( $visit_details->affiliate_id ) && $visit_details->affiliate_id === $affiliate_id ) {
				setcookie( 'affwp_ref', $affiliate_id, strtotime( '+' . $cookie_expiration . ' days' ), '/' );
				setcookie( 'affwp_ref_visit_id', $affiliate_visited_id, strtotime( '+' . $cookie_expiration . ' days' ), '/' );
			}
			if ( $affwp_campaign ) {
				setcookie( 'affwp_campaign', $affwp_campaign, strtotime( '+' . $cookie_expiration . ' days' ), '/' );
			}
		}

	}

	/**
	 * Retrieves a row from the database based on a given row ID.
	 *
	 * @param  int $visit_id Row ID.
	 * @return array|null|object|void
	 */
	public function get_visit( $visit_id ) {
		global $wpdb;
		$visits_table = $wpdb->prefix . 'affiliate_wp_visits';
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $visits_table . ' WHERE visit_id = %s LIMIT 1;', $visit_id ) ); // phpcs:ignore
	}

	/**
	 * Get the current page URL.
	 *
	 * @return string
	 */
	public function current_location() {
		if ( isset( $_SERVER['HTTPS'] ) &&
			( 'on' === $_SERVER['HTTPS'] || 1 === $_SERVER['HTTPS'] ) ||
			isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) &&
			'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
			$protocol = 'https://';
		} else {
			$protocol = 'http://';
		}
		return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Record referral visit via ajax
	 *
	 * @since 1.0
	 */
	public function track_visit_sender() {

		$settings = get_option( CDTAWP_SETTINGS_GROUP );
		$ref_var  = $this->get_option( 'cdtawp_referral_variable' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$affiliate_id = isset( $_GET[ $ref_var ] ) ? absint( $_GET[ $ref_var ] ) : 0;

		if ( ! $affiliate_id || ( ! isset( $settings['cdtawp_referral_credit_last'] ) && isset( $_COOKIE['affwp_visit_id'] ) && isset( $_COOKIE['affwp_affiliate_id'] ) ) ) {
			return;
		}

		$campaign = isset( $_GET['campaign'] ) ? sanitize_text_field( wp_unslash( $_GET['campaign'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$referrer = ! empty( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

		$this->record_visit( $affiliate_id, $campaign, $this->current_location(), $referrer );
	}

	/**
	 * Record referral visit over AJAX (cache-safe).
	 *
	 * admin-ajax.php responses are never cached, so the Set-Cookie headers we
	 * emit here survive full-page caches / CDNs that strip cookies from
	 * cacheable landing-page responses.
	 *
	 * No nonce is verified: this is a public visit-tracking endpoint and a
	 * nonce would be embedded in (potentially cached) page HTML, defeating the
	 * cache-safe purpose of the request. This matches the original query-string
	 * tracking, which was also unauthenticated. The visit creation call to the
	 * parent site is still authenticated with the stored API keys.
	 *
	 * @since 1.0.6
	 */
	public function ajax_track_visit() {

		$settings = get_option( CDTAWP_SETTINGS_GROUP );
		$ref_var  = $this->get_option( 'cdtawp_referral_variable' );

		$affiliate_id = isset( $_POST[ $ref_var ] ) ? absint( wp_unslash( $_POST[ $ref_var ] ) ) : 0;

		if ( ! $affiliate_id || ( ! isset( $settings['cdtawp_referral_credit_last'] ) && isset( $_COOKIE['affwp_visit_id'] ) && isset( $_COOKIE['affwp_affiliate_id'] ) ) ) {
			wp_send_json_error();
		}

		$campaign     = isset( $_POST['campaign'] ) ? sanitize_text_field( wp_unslash( $_POST['campaign'] ) ) : '';
		$landing_page = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
		$referrer     = isset( $_POST['referrer'] ) ? esc_url_raw( wp_unslash( $_POST['referrer'] ) ) : '';

		$data = $this->record_visit( $affiliate_id, $campaign, $landing_page, $referrer );

		wp_send_json_success( $data );
	}

	/**
	 * Set the tracking cookies and create the visit on the parent site.
	 *
	 * Shared by the server-side ( track_visit_sender() ) and AJAX
	 * ( ajax_track_visit() ) entry points.
	 *
	 * @param  int    $affiliate_id Affiliate ID from the referral link.
	 * @param  string $campaign     Campaign name.
	 * @param  string $landing_page Full landing page URL.
	 * @param  string $referrer     Referring URL.
	 * @return array                Tracking data ( affiliate_id, visit_id, campaign ).
	 * @since  1.0.6
	 */
	public function record_visit( $affiliate_id, $campaign = '', $landing_page = '', $referrer = '' ) {

		$settings    = get_option( CDTAWP_SETTINGS_GROUP );
		$cookie_time = strtotime( '+' . $this->get_option( 'cdtawp_cookie_expiration' ) . ' day' );

		$cookie_affiliate_id   = isset( $_COOKIE['affwp_affiliate_id'] ) ? absint( $_COOKIE['affwp_affiliate_id'] ) : 0;
		$cookie_affwp_campaign = isset( $_COOKIE['affwp_campaign'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['affwp_campaign'] ) ) : '';

		setcookie( 'affwp_affiliate_id', $affiliate_id, $cookie_time, '/' );

		if ( ! $cookie_affwp_campaign && $campaign ) {
			setcookie( 'affwp_campaign', $campaign, $cookie_time, '/' );
		}

		$data = array(
			'affiliate_id' => $affiliate_id,
			'campaign'     => $campaign,
			'visit_id'     => isset( $_COOKIE['affwp_visit_id'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['affwp_visit_id'] ) ) : '',
		);

		if ( $affiliate_id !== $cookie_affiliate_id && $affiliate_id ) {

			// Honour the "credit first referrer" policy.
			if ( ! isset( $settings['cdtawp_referral_credit_last'] ) && $cookie_affiliate_id ) {
				setcookie( 'affwp_affiliate_id', $cookie_affiliate_id, $cookie_time, '/' );
				$data['affiliate_id'] = $cookie_affiliate_id;
			}
			setcookie( 'affwp_campaign', $campaign, $cookie_time, '/' );

			$landing_parts = explode( '?', $landing_page );
			$landing_page  = $landing_parts[0];
			$store_url     = $this->get_option( 'cdtawp_store_url' ) . '/wp-json/affwp/v1/visits';

			$pload = array(
				'method'      => 'POST',
				'timeout'     => 30,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(
					'Authorization' => 'Basic ' . base64_encode( AFFILIATE_WP_REST_UN . ':' . AFFILIATE_WP_REST_PWD ), // phpcs:ignore
				),
				'body'        => '',
				'cookies'     => array(),
			);

			$store_url = add_query_arg(
				array(
					'affiliate_id' => $affiliate_id,
					'ip'           => $this->get_ip(),
					'url'          => esc_url_raw( $landing_page ),
					'campaign'     => $campaign,
					'referrer'     => $referrer,
				),
				$store_url
			);

			$response = wp_remote_post( $store_url, $pload );
			$code     = wp_remote_retrieve_response_code( $response );

			if ( ! is_wp_error( $response ) && ( 201 === $code || 200 === $code ) ) {
				$body = json_decode( wp_remote_retrieve_body( $response ) );
				if ( isset( $body->visit_id ) ) {
					setcookie( 'affwp_visit_id', $body->visit_id, $cookie_time, '/' );
					$data['visit_id'] = $body->visit_id;
				}
			}
		}

		return $data;
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
