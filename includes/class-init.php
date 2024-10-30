<?php
/**
 * This file contains the code class initialisaiton
 *
 * @package iZooto web push
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class file the code for save izooto's setting into db
 *
 * @package iZooto web push
 */
class Init {
	/**
	 * Holds the the settings.
	 *
	 * @var options string using constructor.
	 */
	private $options = array();
	private $options_pre;
    private $options_key;
	/**
	 * Assigning string using constructor.
	 */
	public function __construct() {
		$this->set_options_pre('iz_options_');
        $this->set_options_key('izooto-settings');
	}

	/**
	 * Assigning string using constructor.
	 */
	public function get_options_pre() {
		return $this->options_pre;
	}

	/**
	 * Getting  the value of options_key.
	 */
	public function get_options_key() {
        return $this->options_key;
    }

	/**
	 * Setting  the value of options_pre.
	 */
	public function set_options_pre($options_pre) {
        $this->options_pre = $options_pre;
    }

	/**
	 * Setting  the value of options_key.
	 */
    public function set_options_key($options_key) {
        $this->options_key = $options_key;
    }

	/**
	 * Get setting by key.
	 *
	 * @param string  $key as param.
	 * @param boolean $fetch as param.
	 */
	public function izooto_get_option( $key, $fetch = false ) {
		if ( false === $fetch ) {
			$transient_key  = $this->get_options_pre() . $key;
			$transient_body = $this->izooto_transient_get( $transient_key );
			if ( false !== $transient_body ) {
				$option = $transient_body;
			} else {
				$option        = get_option( $key );
				$transient_key = $this->get_options_pre() . $key;
				$this->izooto_transient_set( $transient_key, $option );
			}
		} else {
			$option = get_option( $key );
		}
		return $option;
	}

	/**
	 * Update value by key in izooto tbl.
	 *
	 * @param string  $key as param.
	 * @param string  $option as param.
	 * @param boolean $cache as param true by default.
	 */
	public function izooto_update_option( $key, $option, $cache = true ) {
		$resp = update_option( $key, $option );
		if ( false !== $resp && true === $cache ) {
			$transient_key = $this->get_options_pre() . $key;
			$this->izooto_transient_set( $transient_key, $option );
		}
		return $resp;
	}

	/**
	 * Add value by key in izooto tbl.
	 *
	 * @param string  $key as param.
	 * @param string  $option as param.
	 * @param boolean $cache as param true by default.
	 */
	public function izooto_add_option( $key, $option, $cache = true ) {
		$resp = add_option( $key, $option );
		if ( $resp && true === $cache ) {
			$transient_key = $this->get_options_pre() . $key;
			$this->izooto_transient_set( $transient_key, $option );
		}
		return $resp;
	}

	/**
	 * Cleate izooto's table in db.
	 */
	public static function izooto_create_notification_tbl() {
			global $wpdb;
			$table_name      = $wpdb->prefix . 'iz_notifications_onpush';
			$charset_collate = $wpdb->get_charset_collate();
			$sql             = "CREATE TABLE IF NOT EXISTS $table_name (
				id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				post_id bigint(20) NOT NULL,
				title  varchar(60) NOT NULL default '',
				message varchar(150) NOT NULL default '',
				banner_url text ) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

	}

	/**
	 * Safely use WordPress wp remote get.
	 *
	 * @param string $url as param.
	 */
	public static function izooto_wp_remote_get( $url ) {
		$args     = array( 'timeout' => 15 );
		$response = wp_safe_remote_get( $url, $args );
		return $response;
	}


	/**
	 * Show izooto installation alert.
	 */
	public function izooto_install_alert() {
		$wpurl    = get_bloginfo( 'wpurl' );
		$email    = get_bloginfo( 'admin_email' );
		$wpurl    = ( '' !== $wpurl ) ? $wpurl : '';
		$email    = ( '' !== $email ) ? $email : '';
		$wpurl    = esc_url_raw( $wpurl );
		$url      = IZ_WP_API . '?ref=wp&act=install&url=' . rawurlencode( $wpurl ) . '&email=' . rawurlencode( sanitize_email( $email ) ) . '&izversion=' . IZVERSION;
		$response = self::izooto_wp_remote_get( $url );
	}

	/**
	 * Show izooto uninstallation alert.
	 */
	public function izooto_uninstall_alert() {
		$wpurl    = get_bloginfo( 'wpurl' );
		$email    = get_bloginfo( 'admin_email' );
		$wpurl    = ( '' !== $wpurl ) ? $wpurl : '';
		$email    = ( '' !== $email ) ? $email : '';
		$wpurl    = esc_url_raw( $wpurl );
		$url      = IZ_WP_API . '?ref=wp&act=uninstall&url=' . rawurlencode( $wpurl ) . '&email=' . rawurlencode( sanitize_email( $email ) ) . '&izversion=' . IZVERSION;
		$response = self::izooto_wp_remote_get( $url );
	}

	/**
	 * Show wocommerce installation alert.
	 */
	public function izooto_wcom_install_alert() {
		$wpurl    = get_bloginfo( 'wpurl' );
		$email    = get_bloginfo( 'admin_email' );
		$wpurl    = ( '' !== $wpurl ) ? $wpurl : '';
		$email    = ( '' !== $email ) ? $email : '';
		$wpurl    = esc_url_raw( $wpurl );
		$url      = IZ_WP_API . '?ref=wp&cref=wcom&act=install&url=' . rawurlencode( $wpurl ) . '&email=' . rawurlencode( sanitize_email( $email ) ) . '&izversion=' . IZVERSION;
		$response = self::izooto_wp_remote_get( $url );
	}

	/**
	 * Show wocommerce uninstallation alert.
	 */
	public function izooto_wcom_uninstall_alert() {
		$wpurl    = get_bloginfo( 'wpurl' );
		$email    = get_bloginfo( 'admin_email' );
		$wpurl    = ( '' !== $wpurl ) ? $wpurl : '';
		$email    = ( '' !== $email ) ? $email : '';
		$wpurl    = esc_url_raw( $wpurl );
		$url      = IZ_WP_API . '?ref=wp&cref=wcom&act=uninstall&url=' . rawurlencode( $wpurl ) . '&email=' . rawurlencode( sanitize_email( $email ) ) . '&izversion=' . IZVERSION;
		$response = self::izooto_wp_remote_get( $url );
	}

	/**
	 * Empty izooto's flag.
	 */
	public function izooto_empty_config() {
		$izooto_op          = array();
		$izooto_op['url']   = '';
		$izooto_op['uid']   = '';
		$izooto_op['pid']   = '';
		$izooto_op['cdn']   = '';
		$izooto_op['sw']    = '';
		$izooto_op['gcm']   = '';
		$izooto_op['token'] = '';
		return $izooto_op;
	}

	/**
	 * Get config from izooto id and cache it
	 *
	 * @param string $key as param.
	 */
	public function izooto_request( $key ) {
		$body           = array();
		$key            = sanitize_text_field( $key );
		$transient_key  = 'iz_config_' . $key;
		$transient_body = $this->izooto_transient_get( $transient_key );
		if ( false !== $transient_body ) {// if cached.
			$response = $transient_body;
		} else {
			$args     = array( 'timeout' => 15 );
			$response = wp_safe_remote_get( IZ_WP_API . '?key=' . rawurlencode( $key ) . '&izversion=' . IZVERSION, $args );
			if ( wp_remote_retrieve_response_code( $response ) === 200 ) {
				$this->izooto_transient_set( $transient_key, $response, 60 * 60 );
			}
		}
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			self::izooto_add_action( 'admin_notices', 'izooto_error_token' );
		}
		if ( is_array( $response ) ) {
			$header = $response['headers']; // array of http header.
			$body   = $response['body']; // content.
		}
		return $body;
	}

	/**
	 * Accepts key to check if transient exists if not invokes callback function else returns false
	 *
	 * @param string $key as param.
	 */
	public function izooto_transient_get( $key ) {
		$response = get_transient( $key );
		return $response;
	}

	/**
	 * 12 hours expire time
	 *
	 * @param string $key as param.
	 * @param string $value as param.
	 * @param number $exp_time as param.
	 */
	public function izooto_transient_set( $key, $value, $exp_time = 43200 ) {
		$response = set_transient( $key, $value, $exp_time );
		return $response;
	}

	/**
	 * Removed transied by key
	 *
	 * @param string $key as param.
	 */
	public function izooto_transient_del( $key ) {
		$response = delete_transient( $key );
		return $response;
	}

	/**
	 * Invalid token alert method (Admin)
	 */
	public static function invalid_token() {
		add_settings_error( 'izooto-notice-invalid', esc_attr( 'invalid' ), 'You have entered an invalid iZooto ID. Reverify your iZooto ID. Contact support@izooto.com for any queries.', 'error' );
		settings_errors( 'izooto-notice-invalid' );
	}

	/**
	 * Empty token alert method (Admin)
	 */
	public static function empty_token() {
		add_settings_error( 'izooto-notice-empty', esc_attr( 'empty' ), 'iZooto ID cannot be empty. Submit your iZooto ID to activate web push. Contact support@izooto.com for any queries.', 'error' );
		settings_errors( 'izooto-notice-empty' );
	}

	/**
	 * Sucsess alert method (Admin)
	 */
	public static function izooto_notice_messages() {
		add_settings_error( 'izooto-notice-updated', esc_attr( 'api-updated' ), 'iZooto web push has been successfully activated on your WordPress site.', 'updated' );
		settings_errors( 'izooto-notice-updated' );
	}

	/**
	 * Custom add_action to invoke class function
	 *
	 * @param string   $handle hook name.
	 * @param function $func_name name.
	 * @param boolean  $static name.
	 */
	public static function izooto_add_action( $handle, $func_name, $static = true ) {
		if ( true === $static ) {
			$func_name = __CLASS__ . '::' . $func_name;
		}
		add_action( $handle, $func_name );
	}

	/**
	 * Error token alert method (Admin)
	 */
	public static function izooto_error_token() {
		add_settings_error( 'izooto-notice-validate', 'invalid', 'Could not validate izooto id. Please try again later', 'error' );
		settings_errors( 'izooto-notice-validate' );
	}

}
// AJAX Bind.
add_action( 'wp_ajax_error_alert', 'error_alert' );


/**
 * Global error alert method
 */
function error_alert() {
	$action = filter_input( INPUT_POST, 'action' );
	if ( 'error_alert' !== $action ) {
		echo false;
		wp_die();
	}
	$msg                = filter_input( INPUT_POST, 'message' );
	$endpoint           = IZ_WP_API . '?ref=wp&act=error_alert&izversion=' . IZVERSION;
	$email              = get_bloginfo( 'admin_email' );
	$user               = get_user_by( 'email', $email )->data;
	$user['user_email'] = $email;
	$user_info          = wp_json_encode( $user );
	$wp_site_url        = esc_url( get_site_url() );

	$endpoint .= '&email=' . rawurlencode( $email ) . '&url=' . rawurlencode( $wp_site_url ) . '&userdetails=' . rawurlencode( $user_info ) . '&message=' . $msg;
	$status    = Init::izooto_wp_remote_get( $endpoint );
	$response  = array(
		'status'          => 'logged',
		'WordPress Email' => $email,
		'WordPress Site'  => $wp_site_url,
		'UserDetails'     => $user,
	);
	echo wp_json_encode( $status );
	wp_die();
}

// Main.
$izooto      = new Init();
$tokensubmit = filter_input( INPUT_POST, 'tokensubmit' );
if ( isset( $tokensubmit ) && sanitize_text_field( $tokensubmit ) ) {
	$err       = 0;
	$izooto_op = array();
	$token     = filter_input( INPUT_POST, 'token' );
	if ( empty( $token ) || '' === sanitize_text_field( $token ) ) {
		$err       = 1;
		$izooto_op = $izooto->izooto_empty_config();
		$izooto->izooto_update_option( 'izooto-settings', $izooto_op );
		Init::izooto_add_action( 'admin_notices', 'empty_token' );
	}
	if ( 0 === $err ) {
		$get_json = $izooto->izooto_request( sanitize_text_field( $token ) );
		$get_user = json_decode( $get_json, true );
		if ( 0 !== $get_user['error'] ) {
			$err       = 1;
			$izooto_op = $izooto->izooto_empty_config();
			Init::izooto_add_action( 'admin_notices', 'invalid_token' );
			$izooto->izooto_update_option( 'izooto-settings', $izooto_op );
		}
	}

	if ( 0 === $err && 0 === $get_user['error'] ) {
		$izooto_op['url']   = $get_user['url'];
		$izooto_op['uid']   = $get_user['uid'];
		$izooto_op['pid']   = $get_user['pid'];
		$izooto_op['cdn']   = $get_user['js-url'];
		$izooto_op['sw']    = $get_user['sw-url'];
		$izooto_op['gcm']   = $get_user['gcmSenderId'];
		$izooto_op['token'] = sanitize_text_field( $token );

		$izooto->izooto_update_option( 'izooto-settings', $izooto_op );
		Init::izooto_add_action( 'admin_notices', 'izooto_notice_messages' );
		$fresh_user = filter_input( INPUT_POST, 'freshUser' );
		if ( 1 === sanitize_text_field( $fresh_user ) ) {
			echo '<script>var newUser=1;</script>';
		}
	}
}
