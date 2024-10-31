<?php
/**
 * Plugin Name:          Role Based Pricing for WooCommerce
 * Plugin URI:           https://webfixlab.com/plugins/simple-variation-swatches/
 * Description:          EASY to use and super FAST WooCommerce product role based pricing solution to add different prices for different roles.
 * Author:               WebFix Lab
 * Author URI:           https://webfixlab.com/
 * Version:              4.0.1
 * Requires at least:    4.9
 * Tested up to:         6.6.1
 * Requires PHP:         7.0
 * Tags:                 role based pricing, dynamic pricing, wholesale pricing, prices by user role, hide price
 * WC requires at least: 3.6
 * WC tested up to:      9.1.4
 * License:              GPL2
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          product-role-rules
 * Domain Path:          /languages
 *
 * @package              Role Based Pricing for WooCommerce
 */

defined( 'ABSPATH' ) || exit;

// plugin path.
define( 'PROLER', __FILE__ );
define( 'PROLER_VER', '4.0.1' );
define( 'PROLER_PATH', plugin_dir_path( PROLER ) );

require PROLER_PATH . 'includes/class/admin/class-prolerloader.php';
