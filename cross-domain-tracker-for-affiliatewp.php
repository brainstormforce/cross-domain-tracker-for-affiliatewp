<?php
/**
 * Plugin Name: Cross Domain Tracker for AffiliateWP
 * Plugin URI:
 * Description: Track referrals from different domains.
 * Author: Pratik Chaskar
 * Author URI: https://pratikchaskar.com
 * Version: 1.0.5
 * Text Domain: affiliatewp-external-visits
 * Domain Path: languages
 * WC requires at least: 3.0
 * WC tested up to: 5.1.0
 * PHP version 5
 *
 * @category PHP
 * @package  Affiliate_WP_Visits_Tracking
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set constants.
 */
define( 'AFILIATE_WP_EXTERNAL_VISITS_FILE', __FILE__ );

/**
 * Loader
 */
require_once 'includes/class-affiliate-wp-track-external-visits.php';
