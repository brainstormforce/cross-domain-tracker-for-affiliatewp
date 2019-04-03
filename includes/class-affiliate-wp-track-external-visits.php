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
	public static  $plugin_dir;

	/**
	 *  URL of plugin directory.
	 *
	 * @var plugin_url.
	 */
	public static  $plugin_url;

	/**
	 *  Version of plugin.
	 *
	 * @var version.
	 */
	private static $version;


	/**
	 * Main AffiliateWP_Track_External_Visits Instance
	 *
	 * Insures that only one instance of AffiliateWP_Track_External_Visits exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @return The one true AffiliateWP_Track_External_Visits
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Affiliate_WP_Track_External_Visits ) ) {
			self::$instance   = new Affiliate_WP_Track_External_Visits;
			self::$plugin_dir = plugin_dir_path( AFILIATE_WP_EXTERNAL_VISITS_FILE );
			self::$plugin_url = plugin_dir_url( AFILIATE_WP_EXTERNAL_VISITS_FILE );
			self::$version    = '1.0.0';
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
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Something went wrong!', 'affiliatewp-track-external-visits' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Something went wrong!', 'affiliatewp-track-external-visits' ), '1.0' );
	}

	/**
	 * Include necessary files
	 *
	 * @access      private
	 * @since       1.0.0
	 * @return      void
	 */
	public function includes() {

		if ( ! is_admin() ) {
			require_once self::$plugin_dir . 'includes/class-affiliate-wp-visits-tracking.php';
			$visit_tracking = new Affiliate_WP_Visits_Tracking();
			$visit_tracking->track_visit();
		}
	}

}

/**
 * The main function responsible for returning the one true AffiliateWP_Track_External_Visits
 * Instance to functions everywhere.
 *
 * @since 1.0
 * @return object The one true AffiliateWP_Track_External_Visits Instance
 */
function affiliate_wp_track_external_visits() {
	return Affiliate_WP_Track_External_Visits::instance();
}
add_action( 'plugins_loaded', 'affiliate_wp_track_external_visits', 100 );
