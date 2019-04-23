<?php
/**
 * Main loader.
 *
 * @package Affiliate_WP_Visits_Tracking
 */

/**
 * Class AffiliateWP_Track_External_Visits.
 */
final class Affiliate_WP_Track_External_Visits {


	/**
	 * Instance object.
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Path of plugin directory.
	 *
	 * @var plugin_dir
	 */
	public static $plugin_dir;

	/**
	 *  URL of plugin directory.
	 *
	 * @var plugin_url.
	 */
	public static $plugin_url;

	/**
	 *  Version of plugin.
	 *
	 * @var version.
	 */
	public static $version;

	/**
	 * Get things started
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->define_constants();

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'settings' ) );

		add_action( 'admin_init', array( $this, 'add_style_scripts' ) );

		add_action( 'admin_notices', array( $this, 'check_store_connection' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( AFILIATE_WP_EXTERNAL_VISITS_FILE ), array( $this, 'add_action_links' ) );

	}

	/**
	 *  Show actions on the plugin page.
	 *
	 * @param array $links links.
	 * @return array
	 */
	function add_action_links( $links ) {

		$mylinks = array(
			'<a href="' . admin_url( 'options-general.php?page=external-visits' ) . '">Settings</a>',
		);
		return array_merge( $links, $mylinks );
	}

	/**
	 * Get options
	 *
	 * @param string $option options.
	 * @since 1.0.0
	 */
	private function get_option( $option = '' ) {
		$options = get_option( CDTAWP_SETTINGS_GROUP );

		if ( ! isset( $option ) ) {
			return;
		}

		return $options[ $option ];
	}


	/**
	 *  Check store connection.
	 *
	 * @since 1.0.0
	 */
	public function check_store_connection() {

		$plugin_type = $this->get_option( 'cdtawp_plugin_type' );
		if ( ( ! isset( $_GET['page'] ) || CDTAWP_PAGE != $_GET['page'] ) || ( CDTAWP_PLUGIN_CHILD != $plugin_type || ! is_admin() ) ) {
			return;
		}

		$landing_page = $this->get_option( 'cdtawp_store_url' );
		$store_url    = $landing_page . '/wp-json/affwp/v1/visits';

		$pload = array(
			'method'      => 'GET',
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

		$response = wp_remote_get( $store_url, $pload );
		$code     = wp_remote_retrieve_response_code( $response );

		if ( 200 != $code && 201 != $code ) {
			$class   = 'notice notice-warning is-dismissible';
			$message = __( 'Please update & verify your settings to connect with store!', 'affiliatewp-external-visits' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} else {

			$class   = 'notice notice-success is-dismissible';
			$message = __( 'Connected with store successfully!', 'affiliatewp-external-visits' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );

		}

	}

	/**
	 *  Add scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function add_style_scripts() {
		wp_enqueue_script( 'cdtawp-script', self::$plugin_url . 'assets/js/admin-settings.js', array( 'jquery' ), self::$version );

	}

	/**
	 * Define all required constants.
	 *
	 * @since 1.0
	 */
	private function define_constants() {
		define( 'CDTAWP_PAGE', 'external-visits' );
		define( 'CDTAWP_SETTINGS_GROUP', 'cdtawp_settings' );
		define( 'CDTAWP_SECTION', 'cdtawp_settings_section' );
		define( 'CDTAWP_CONNECTION_SECTION', 'cdtawp_connection_settings_section' );

		define( 'CDTAWP_PLUGIN_CHILD', 'Child' );
		define( 'CDTAWP_PLUGIN_PARENT', 'Parent' );

	}

	/**
	 * Register menu
	 *
	 * @since 1.0.0
	 */
	public function register_menu() {
		add_options_page(
			__( 'Cross Domain Tracker for AffiliateWP', 'affiliatewp-external-visits' ),
			__( 'Cross Domain Tracker for AffiliateWP', 'affiliatewp-external-visits' ),
			'manage_options',
			CDTAWP_PAGE,
			array( $this, 'admin_page_view_callback' )
		);
	}

	/**
	 * Admin page
	 *
	 * @since 1.0.0
	 */
	public function admin_page_view_callback() {
		?>


		<div class="wrap">


			<h2>
		<?php _e( 'Cross Domain Tracker for AffiliateWP', 'affiliatewp-external-visits' ); ?>
			</h2>

			<form action="options.php" method="POST">
		<?php
		settings_fields( CDTAWP_SETTINGS_GROUP );
		do_settings_sections( CDTAWP_PAGE );
		submit_button();
		?>

			</form>

		</div>
		<?php
	}

	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	public function settings() {
		if ( false == get_option( CDTAWP_SETTINGS_GROUP ) ) {
			add_option( CDTAWP_SETTINGS_GROUP, $this->default_options() );
		}

		add_settings_section(
			CDTAWP_SECTION,
			__( 'General Settings', 'affiliatewp-external-visits' ),
			array( $this, 'cdtawp_section_callback' ),
			CDTAWP_PAGE
		);

		// URL to search for.
		add_settings_field(
			'Use Plugin As',
			__( 'Use Plugin As', 'affiliatewp-external-visits' ),
			array( $this, 'callback_input_dropdown' ),
			CDTAWP_PAGE,
			CDTAWP_SECTION,
			array(
				'name'        => 'cdtawp_plugin_type',
				'id'          => 'cdtawp_plugin_type',
				'description' => __( 'Parent - Select this option on the main website where you have AffiliateWP plugin installed and where conversions take place. <br/> Child - Select this option on a marketing website where your affiliates send traffic. ', 'affiliatewp-external-referral-links' ),
			)
		);

		// URL to search for.
		add_settings_field(
			'Site URL',
			__( 'Site URL', 'affiliatewp-external-visits' ),
			array( $this, 'callback_input_text' ),
			CDTAWP_PAGE,
			CDTAWP_SECTION,
			array(
				'name'        => 'cdtawp_store_url',
				'id'          => 'cdtawp_store_url',
				'description' => __( 'The site URL where AffiliateWP is actually installed.', 'affiliatewp-external-referral-links' ),
			)
		);

		// Referral Variable.
		add_settings_field(
			'Referral Variable',
			__( 'Referral Variable', 'affiliatewp-external-visits' ),
			array( $this, 'callback_input_text' ),
			CDTAWP_PAGE,
			CDTAWP_SECTION,
			array(
				'name'        => 'cdtawp_referral_variable',
				'id'          => 'cdtawp_referral-variable',
				'description' => __( 'The referral variable you have set in AffiliateWP at the site URL above. It must match exactly.', 'affiliatewp-external-referral-links' ),
			)
		);

		// Cookie Expiration.
		add_settings_field(
			'Cookie Expiration',
			__( 'Cookie Expiration', 'affiliatewp-external-visits' ),
			array( $this, 'callback_input_number' ),
			CDTAWP_PAGE,
			CDTAWP_SECTION,
			array(
				'name'        => 'cdtawp_cookie_expiration',
				'id'          => 'cdtawp_cookie_expiration',
				'description' => __( 'How many days should the referral tracking cookie be valid for?', 'affiliatewp-external-referral-links' ),
			)
		);

		// Credit last referral.
		add_settings_field(
			'Credit Last Referral',
			__( 'Credit Last Referrer', 'affiliatewp-external-visits' ),
			array( $this, 'callback_input_checkbox' ),
			CDTAWP_PAGE,
			CDTAWP_SECTION,
			array(
				'name'        => 'cdtawp_referral_credit_last',
				'id'          => 'cdtawp_referral_credit_last',
				'description' => __( 'Credit the last affiliate who referred the customer.', 'affiliatewp-external-visits' ),
			)
		);

		// Child plugin settings.
		add_settings_section(
			CDTAWP_CONNECTION_SECTION,
			__( 'Store Connection Settings', 'affiliatewp-external-visits' ),
			array( $this, 'cdtawp_section_callback' ),
			CDTAWP_PAGE
		);

		add_settings_field(
			'Public Key',
			__( 'Public Key', 'affiliatewp-external-visits' ),
			array( $this, 'callback_input_text' ),
			CDTAWP_PAGE,
			CDTAWP_CONNECTION_SECTION,
			array(
				'name'        => 'cdtawp_public_key',
				'id'          => 'cdtawp_public_key',
				'description' => __( 'Generate API key and copy/paste public key from your store where AffiliateWP is installed!', 'affiliatewp-external-visits' ),
			)
		);

		add_settings_field(
			'Token',
			__( 'Token', 'affiliatewp-external-visits' ),
			array( $this, 'callback_input_text' ),
			CDTAWP_PAGE,
			CDTAWP_CONNECTION_SECTION,
			array(
				'name'        => 'cdtawp_token',
				'id'          => 'cdtawp_token',
				'description' => __( 'Generate API key and copy/paste token from your store where AffiliateWP is installed!', 'affiliatewp-external-visits' ),
			)
		);

		register_setting(
			CDTAWP_SETTINGS_GROUP,
			CDTAWP_SETTINGS_GROUP
		);

	}


	/**
	 * Callback for cart abandonment options.
	 *
	 * @since 1.0.0
	 */
	public function cdtawp_section_callback() {
	}


	/**
	 * Number Input field callback
	 *
	 * @param array $args agruments.
	 * @since 1.0.0
	 */
	public function callback_number_input( $args ) {

		$options = get_option( CDTAWP_SETTINGS_GROUP );
		$value   = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : '';
		?>
		<input type="number" id="<?php echo $args['id']; ?>" name="affiliatewp_external_referral_links[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" class="small-text" min="0" max="999999" step="1"/>

		<?php if ( isset( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
		<?php endif; ?>
		<?php

	}

	/**
	 * Default values
	 *
	 * @since 1.0.0
	 */
	public function default_options() {
		$defaults = array(
			'cdtawp_plugin_type'          => CDTAWP_PLUGIN_CHILD,
			'cdtawp_cookie_expiration'    => '30',
			'cdtawp_referral_variable'    => 'ref',
			'cdtawp_referral_credit_last' => true,
			'cdtawp_store_url'            => '',
			'cdtawp_public_key'           => '',
			'cdtawp_token'                => '',
		);

		return apply_filters( 'cdtawp_default_options', $defaults );

	}

	/**
	 * Input field callback
	 *
	 * @param array $args arguments.
	 * @since 1.0.0
	 */
	public function callback_input_text( $args ) {

		$options = get_option( CDTAWP_SETTINGS_GROUP );

		$value = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : '';
		?>
		<input style="width: 30%" type="text" id="<?php echo $args['id']; ?>" name="<?php echo CDTAWP_SETTINGS_GROUP; ?>[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>"/>

		<?php if ( isset( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
		<?php endif; ?>
		<?php

	}

	/**
	 * Input field callback
	 *
	 * @param array $args arguments.
	 * @since 1.0.0
	 */
	public function callback_input_number( $args ) {

		$options = get_option( CDTAWP_SETTINGS_GROUP );

		$value = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : '';
		?>
		<input style="width: 10%" type="number" id="<?php echo $args['id']; ?>" name="<?php echo CDTAWP_SETTINGS_GROUP; ?>[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>"/>

		<?php if ( isset( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
		<?php endif; ?>
		<?php

	}

	/**
	 * Input field callback
	 *
	 * @param array $args arguments.
	 * @since 1.0.0
	 */
	public function callback_input_dropdown( $args ) {

		$options = get_option( CDTAWP_SETTINGS_GROUP );

		$value = isset( $options[ $args['name'] ] ) ? $options[ $args['name'] ] : '';
		?>

		<select id="<?php echo $args['id']; ?>" name='<?php echo CDTAWP_SETTINGS_GROUP; ?>[<?php echo $args['name']; ?>]'>
			<option value='<?php echo( CDTAWP_PLUGIN_CHILD ); ?>' <?php selected( $value, CDTAWP_PLUGIN_CHILD ); ?>> <?php _e( 'Child', 'affiliatewp-external-visits' ); ?> </option>
			<option value='<?php echo( CDTAWP_PLUGIN_PARENT ); ?>' <?php selected( $value, CDTAWP_PLUGIN_PARENT ); ?>> <?php _e( 'Parent', 'affiliatewp-external-visits' ); ?>  </option>
		</select>
		<?php if ( isset( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
		<?php endif; ?>
		<?php

	}

	/**
	 * Input field callback
	 *
	 * @param array $args arguments.
	 * @since 1.0.0
	 */
	public function callback_input_checkbox( $args ) {

		$options = get_option( CDTAWP_SETTINGS_GROUP );
		$value   = isset( $options[ $args['name'] ] ) ? true : false;

		?>
		<input <?php echo $value ? 'checked' : ''; ?> type="checkbox" id="<?php echo $args['id']; ?>" name="<?php echo CDTAWP_SETTINGS_GROUP; ?>[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>"/>

		<?php if ( isset( $args['description'] ) ) : ?>
		<p class="description"><?php echo $args['description']; ?></p>
		<?php endif; ?>
		<?php

	}

	/**
	 * Main AffiliateWP_Track_External_Visits Instance
	 *
	 * Insures that only one instance of AffiliateWP_Track_External_Visits exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @return    The one true AffiliateWP_Track_External_Visits
	 * @since     1.0.0
	 * @static
	 * @staticvar array $instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Affiliate_WP_Track_External_Visits ) ) {

			self::$plugin_dir = plugin_dir_path( AFILIATE_WP_EXTERNAL_VISITS_FILE );
			self::$plugin_url = plugin_dir_url( AFILIATE_WP_EXTERNAL_VISITS_FILE );

			self::$instance = new Affiliate_WP_Track_External_Visits();
			self::$version  = '1.0.0';
			self::$instance->includes();
		}

		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @return void
	 * @since  1.0.0
	 * @access protected
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Something went wrong!', 'affiliatewp-external-visits' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @return void
	 * @since  1.0.0
	 * @access protected
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Something went wrong!', 'affiliatewp-external-visits' ), '1.0' );
	}

	/**
	 * Include necessary files
	 *
	 * @access private
	 * @return void
	 * @since  1.0.0
	 */
	public function includes() {
		$options = get_option( CDTAWP_SETTINGS_GROUP );

		include_once self::$plugin_dir . 'includes/class-affiliate-wp-visits-tracking.php';
		$visit_tracking = new Affiliate_WP_Visits_Tracking();

		if ( ! is_admin() ) {
			if ( isset( $options['cdtawp_plugin_type'] ) && CDTAWP_PLUGIN_CHILD == $options['cdtawp_plugin_type'] ) {
				// Child plugin send tracked visit.
				$visit_tracking->track_visit_sender();
			} else {
				// Parent plugin receive sent visit.
				$visit_tracking->track_visit_receiver();
			}
		}
	}


}

/**
 * The main function responsible for returning the one true AffiliateWP_Track_External_Visits
 * Instance to functions everywhere.
 *
 * @return object The one true AffiliateWP_Track_External_Visits Instance
 * @since  1.0
 */
function affiliate_wp_track_external_visits() {
	return Affiliate_WP_Track_External_Visits::instance();
}
add_action( 'plugins_loaded', 'affiliate_wp_track_external_visits', -1 );
