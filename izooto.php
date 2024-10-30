<?php
/**
 * Plugin name: iZooto Web Push
 * Plugin URI: https://www.izooto.com
 * Description: Browser push notifications for your site, available in Chrome, Safari and Firefox.
 * Author: iZooto
 * Author URI: https://www.izooto.com
 * Version: 3.7.19
 * License: GPL v2 or later
 *
 * @package izooto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IZOOTO_BASE_URL' ) ) {
	define( 'IZOOTO_BASE_URL', plugin_dir_url( __FILE__ ) );
}

define( 'IZVERSION', '3.7.19' );

define( 'IZ_WP_API', 'https://middlewarev3.izooto.com/wordpress/integrate' );

/**
 * Create izooto object on install & show message
 */
function izooto_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';
	$iz_obj = new Init();
	Init::izooto_create_notification_tbl();
	$iz_obj->izooto_install_alert();
}
register_activation_hook( __FILE__, 'izooto_activate' );

/**
 * On izooto's plugin deactivate
 */
function izooto_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';
	$iz_obj = new Init();
	$iz_obj->izooto_uninstall_alert();
}
register_deactivation_hook( __FILE__, 'izooto_deactivate' );

/**
 * On woocmmerce plugin activate
 */
function izooto_activate_wcom() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';
	$iz_obj              = new Init();
	$iz_settings         = $iz_obj->izooto_get_option( 'izooto-settings' );
	$iz_settings['wcom'] = 1;
	$iz_obj->izooto_update_option( 'izooto-settings', $iz_settings );
	$iz_obj->izooto_wcom_install_alert();
}

/**
 * On woocmmerce plugin deactivate
 */
function izooto_deactivate_wcom() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';
	$iz_obj              = new Init();
	$iz_settings         = $iz_obj->izooto_get_option( 'izooto-settings' );
	$iz_settings['wcom'] = 0;
	$iz_obj->izooto_update_option( 'izooto-settings', $iz_settings );
	$iz_obj->izooto_wcom_uninstall_alert();
}

/**
 * Get cookie data after unslash & sanitization
 *
 * @param string $key cookie name.
 */
function izooto_get_cookie_data( $key ) {
	$output = '';
	if ( isset( $_COOKIE[ $key ] ) ) {
		$output = sanitize_text_field( wp_unslash( $_COOKIE[ $key ] ) );
	}
	return $output;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-init.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/izootosdk.php';

$settings = $izooto->izooto_get_option( 'izooto-settings' );
if ( empty( $settings ) ) {
	$settings = $izooto->izooto_empty_config( 'izooto-settings' );
	$izooto->izooto_add_option( 'izooto-settings', $settings );
} else {
	if ( ( ! empty( $settings['pid'] ) ) && ( ! empty( $settings['token'] ) ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/izootometa.php';
		$wc_status = ( isset( $settings['wcom'] ) ) ? $settings['wcom'] : 0;
		/**
		 * Check if WooCommerce is active
		 */
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/izwoocommevents.php';
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-izwoocommeventshelper.php';
			if ( 0 === $wc_status ) {
				izooto_activate_wcom();
			}
		} elseif ( 1 === $wc_status ) {
			izooto_deactivate_wcom();
		}
	}
}
