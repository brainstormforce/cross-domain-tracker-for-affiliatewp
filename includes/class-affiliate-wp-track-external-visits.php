<?php
/**
 * Main loader.
 *
 * @package Affiliate_WP_Visits_Tracking
 */

// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
/**
 * Class AffiliateWP_Track_External_Visits.
 */
final class Affiliate_WP_Track_External_Visits
{

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
    public static $version;

    /**
     * Get things started
     *
     * @since 1.0
     */
    public function __construct()
    {

        $this->define_constants();

        add_action('admin_menu', array( $this, 'register_menu' ));

        add_action('admin_init', array( $this, 'settings' ));

    }

    /**
     * Define all required constants.
     *
     * @since 1.0
     */
    private function define_constants()
    {

        define('AWP_PAGE', 'external-visits');
        define('AWP_SETTINGS_GROUP', 'awp_settings');
        define('AWP_SECTION', 'awp_settings_section');

    }

    /**
     * Register menu
     *
     * @since 1.0
     */
    public function register_menu()
    {
        add_options_page(
            __('External Visits', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN),
            __('External Visits', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN),
            'manage_options',
            AWP_PAGE,
            array( $this, 'admin_page_view_callback' )
        );
    }
    /**
     * Admin page
     */
    public function admin_page_view_callback()
    {
        ?>
        <div class="wrap">

            <h2><?php _e('AffiliateWP - External Visits', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN); ?></h2>

            <form action="options.php" method="POST">
        <?php
        settings_fields(AWP_SETTINGS_GROUP);
        do_settings_sections(AWP_PAGE);
        ?>

        <?php submit_button(); ?>
            </form>

        </div>
        <?php
    }

    /**
     * Settings
     *
     * @since 1.0
     */
    public function settings()
    {

        if (false == get_option(AWP_SETTINGS_GROUP) ) {
            add_option(AWP_SETTINGS_GROUP, $this->default_options());
        }

        add_settings_section(
            AWP_SECTION,
            '',
            '',
            AWP_PAGE
        );

        add_settings_field(
            'Public Key',
            __('Public Key', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN),
            array( $this, 'callback_input' ),
            AWP_PAGE,
            AWP_SECTION,
            array(
            'name'        => 'public_key',
            'id'          => 'public_key',
            'description' => __('Generate API key and copy/paste public key from your store where AffiliateWP is installed!', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN),
            )
        );

        add_settings_field(
            'Token',
            __('Token', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN),
            array( $this, 'callback_input' ),
            AWP_PAGE,
            AWP_SECTION,
            array(
            'name'        => 'token',
            'id'          => 'token',
            'description' => __('Generate API key and copy/paste token from your store where AffiliateWP is installed!', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN),
            )
        );

        register_setting(
            AWP_SETTINGS_GROUP,
            AWP_SETTINGS_GROUP
        );

    }

    /**
     * Default values
     *
     * @since 1.0
     */
    public function default_options()
    {

        $defaults = array(
        'public_key' => '',
        'token'      => '',
        );

        return apply_filters('awp_default_options', $defaults);

    }

    /**
     * Input field callback
     *
     * @param array $args arguments.
     * @since 1.0
     */
    public function callback_input( $args )
    {

        $options = get_option(AWP_SETTINGS_GROUP);

        $value = isset($options[ $args['name'] ]) ? $options[ $args['name'] ] : '';
        ?>
        <input type="password" id="<?php echo $args['id']; ?>" name="<?php echo AWP_SETTINGS_GROUP; ?>[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" />

        <?php if (isset($args['description']) ) : ?>
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
     * @since     1.0
     * @static
     * @staticvar array $instance
     * @return    The one true AffiliateWP_Track_External_Visits
     */
    public static function instance()
    {


        if (! isset(self::$instance) && ! ( self::$instance instanceof Affiliate_WP_Track_External_Visits ) ) {
            self::$instance   = new Affiliate_WP_Track_External_Visits();
            self::$plugin_dir = plugin_dir_path(AFILIATE_WP_EXTERNAL_VISITS_FILE);
            self::$plugin_url = plugin_dir_url(AFILIATE_WP_EXTERNAL_VISITS_FILE);
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
     * @since  1.0
     * @access protected
     * @return void
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, __('Something went wrong!', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN), '1.0');
    }

    /**
     * Disable unserializing of the class
     *
     * @since  1.0
     * @access protected
     * @return void
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('Something went wrong!', AFILIATE_WP_EXTERNAL_VISITS_TEXT_DOMAIN), '1.0');
    }

    /**
     * Include necessary files
     *
     * @access private
     * @since  1.0.0
     * @return void
     */
    public function includes()
    {

        if (! is_admin() ) {
            include_once self::$plugin_dir . 'includes/class-affiliate-wp-visits-tracking.php';
            $visit_tracking = new Affiliate_WP_Visits_Tracking();
            $visit_tracking->track_visit();
        }
    }

}

/**
 * The main function responsible for returning the one true AffiliateWP_Track_External_Visits
 * Instance to functions everywhere.
 *
 * @since  1.0
 * @return object The one true AffiliateWP_Track_External_Visits Instance
 */
function affiliate_wp_track_external_visits()
{
    return Affiliate_WP_Track_External_Visits::instance();
}
add_action('plugins_loaded', 'affiliate_wp_track_external_visits', 999);
// phpcs:enable WordPress.WP.I18n.NonSingularStringLiteralDomain