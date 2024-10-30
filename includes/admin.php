<?php
/**
 * This file contains the code for ui of izooto's admin page
 *
 * @package iZooto web push
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'register_izooto_scripts' );// To load scripts after finishing the page load and before any headers are sent.
add_action( 'admin_menu', 'izooto_menu' );
add_action( 'admin_enqueue_scripts', 'izooto_enqueue' );

/**
 * Function to register assets.
 */
function register_izooto_scripts() {
	$subpath       = 'unminified';
	$js_base_path  = esc_url( IZOOTO_BASE_URL ) . 'assets/js/' . $subpath;
	$css_base_path = esc_url( IZOOTO_BASE_URL ) . 'assets/css/' . $subpath;
	wp_register_script( 'admin_init', $js_base_path . '/admin_init.js?1', array(), IZVERSION, false );
	wp_register_style( 'admin_style', $css_base_path . '/admin_style.css', array(), IZVERSION, false );
	wp_register_style( 'iznotify_style', $css_base_path . '/iznotify_style.css', array(), IZVERSION, false );
	wp_register_style( 'iz_fonts', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), IZVERSION, false );
	wp_register_script( 'iznotify_script', $js_base_path . '/iznotify_script.js', array(), IZVERSION, false );
	wp_register_script( 'iznotify_editor_script', $js_base_path . '/iznotify_editor_script.js', array(), IZVERSION, false );
	wp_register_script( 'cookies', $js_base_path . '/cookies.js', array(), IZVERSION, false );
}

/**
 * Function to enqueue.
 *
 * @param string $hook custom hook name.
 */
function izooto_enqueue( $hook ) {
	$alert = false;
	if ( 'toplevel_page_izooto-configuration' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'admin_style' );
	wp_enqueue_style( 'iz_fonts' );
	wp_enqueue_script( 'admin_init' );
	$admin_email = get_bloginfo( 'admin_email' );
	$user_obj    = get_user_by( 'email', $admin_email );
	if ( isset( $user_obj ) && isset( $user_obj->data ) ) {
		$admin_user = $user_obj->data;
	} else {
		$admin_user = false;
	}
	$wp_site_url = esc_url( get_site_url() );
	if ( ! $admin_user ) {
		$alert = true;
		$email = wp_get_current_user()->data->user_email;
		$user  = get_user_by( 'email', $email )->data;
	} else {
		$email = $admin_email;
		$user  = get_user_by( 'email', $email )->data;
	}
	$user_info = wp_json_encode( $user );
	$params    = array(
		'user_info'   => $user_info,
		'wp_site_url' => $wp_site_url,
		'wp_email'    => $email,
		'admin_email' => $admin_email,
		'alert'       => $alert,
	);
	wp_localize_script( 'admin_init', 'params', $params );
}


$slip_izooto = filter_input( INPUT_POST, 'slipIzooto' );
if ( isset( $slip_izooto ) && is_admin() ) {
	if ( 'reset' === sanitize_text_field( $slip_izooto ) ) {
		$izooto_op['url']   = '';
		$izooto_op['uid']   = '';
		$izooto_op['pid']   = '';
		$izooto_op['cdn']   = '';
		$izooto_op['sw']    = '';
		$izooto_op['gcm']   = '';
		$izooto_op['token'] = '';
		update_option( 'izooto-settings', $izooto_op );
	}
}

if ( ! function_exists( 'izooto_menu' ) ) {

	/**
	 * Function to add izooto optin in menu.
	 */
	function izooto_menu() {
		$page_title = 'iZooto';
		$menu_title = 'iZooto';
		$capability = 'manage_options';
		$menu_slug  = 'izooto-configuration';
		$function   = 'izooto_fn';
		$icon_url   = IZOOTO_BASE_URL . 'assets/images/icon2.png';
		add_menu_page(
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			$function,
			$icon_url
		);
	}
}

if ( ! function_exists( 'izooto_fn' ) ) {
	/**
	 * Function to add header section in admin page
	 */
	function izooto_fn() {
		$opfunction = get_option( 'izooto-settings' );
		?>

	<!--<div class="plugin-container" style="margin-top: 25px;">
		<div class="plugin-header">
			class="izooto-logo"> <span> Web Push for WordPress</span>
		</div>-->

		<?php izooto_check_sw_exist(); ?>
		<div class="izooto-wordpress">
		<div class="header">
			<img src="<?php echo esc_url_raw( IZOOTO_BASE_URL . 'assets/images/logo.svg' ); ?>">
		</div>
		<div class="body">
			<h4 class="subheading-1">Enable Web Push on your WordPress website</h4>
		<!-- Active status  -->
		<?php
		if ( isset( $opfunction['token'] ) && '' !== $opfunction['token'] ) {
			$site_url = ( isset( $opfunction['site_url'] ) && '' !== $opfunction['site_url'] ) ? $opfunction['site_url'] : $opfunction['url'];
		}
		?>
		<div class="shadow-card card-container-height">
				<div class="section-two">
					<div class="left">
						<div class="grey-border width-400" style="margin-top: 16px;">
							<label>iZooto ID</label>
								<form method="post">
									<div class="inline-content" style="margin-top: 6px;">
								<?php settings_fields( 'izooto-settings' ); ?>
								<?php do_settings_sections( 'izooto-settings' ); ?>
								<?php
								if ( isset( $opfunction['token'] ) && '' !== $opfunction['token'] ) {
									?>
									<input type="text" name="token"  id="token" class="form-control" placeholder="Place iZooto ID here to enable Web Push." style="margin-right: 5px;" value="<?php echo esc_attr( $opfunction['token'] ); ?> " readonly>
									<!--<button id="edit-token"  onclick="editizootoId();"class="stroked-button icon-button" style="margin-left: 5px;"><i class="material-icons" style="font-size: 20px;">edit</i></button>-->

									<a href="javascript:void(0)" id="edit-token" onclick="editizootoId()" class="stroked-button icon-button" style="margin-left: 5px;"><i class="material-icons" style="font-size: 20px;">edit</i></a>
									<button type='submit' style="cursor:pointer;display:none" name='tokensubmit' id='tokensubmit' value='submit' class="primary-button" style="margin-left: 5px; ">Save</button>
									<!--<button type="submit" name="tokensubmit" id="tokensubmit" value="submit" class="stroked-button icon-button" style="margin-left: 5px;"><i class="material-icons" style="font-size: 20px;">edit</i></button>-->
									<?php
								} else {
									?>
								<input type="text" name="token"  id="token" class="form-control" placeholder="Place iZooto ID here to enable Web Push." style="margin-right: 5px;" value="<?php echo esc_attr( $opfunction['token'] ); ?>">
								<button type='submit' style="cursor:pointer;" name='tokensubmit' id='tokensubmit' value='submit' class="primary-button" style="margin-left: 5px;">Save</button>
									<?php
								}
								?>
								<!--<a href="javascript:void(0)" class="primary-button" style="margin-left: 5px;">Save</a>-->
							<input id='freshUser' name='freshUser' hidden>
							</div></form>
						</div>

						<div class="instructions width-500" style="margin-top: 30px;">
							<div>
								<label>Follow the steps below to get your iZooto ID.</label>
								<ol>
									<li class="text-body" style="margin-bottom: 5px;"><a href="https://panel.izooto.com/login" target="_blank">Log in</a> to your existing iZooto account or create a <a href="https://panel.izooto.com/register" target="_blank">new iZooto account</a>.</li>
									<li class="text-body" style="margin-top: 5px;">Go to Settings > Setup > Install using Plugins. Copy the iZooto ID &amp; paste it in the field above.</li>
								</ol>
							</div>
						</div>
					</div>

					<div class="right">
						<div class="align-mid">
							<label style="margin-bottom: 20px;">Verify setup by opening your website in a new tab.</label>
							<!--<img src="prompt-2.svg" width="350">-->
							<img  width="350" src="<?php echo esc_url_raw( IZOOTO_BASE_URL . 'assets/images/prompt-2.svg' ); ?>"
						</div>
					</div>
				</div>
			</div>

			</div>
	</div>
	<div class="izooto-footer">Please contact <a href="mailto: support@izooto.com">support@izooto.com</a> for queries</div>
	<!--</div>
	<div class="plugin-footer">Please contact support@izooto.com, if you run into any issues.</div>-->
		<?php
	}
}
/**
 * Function to show sw not found notice on header
 */
function izooto_show_sw_not_found_notice() {
	add_settings_error( 'izooto-notice-sw', esc_attr( 'sw-file-not-found' ), 'It is recommended to host the Service Worker file on your domain to avoid any potential concerns.', 'warning' );
	settings_errors( 'izooto-notice-sw' );
}

/**
 * Function to check where sw placed on root or not
 */
function izooto_check_sw_exist() {
	$path = ABSPATH . 'service-worker.js';
	if ( ! file_exists( $path ) ) {
		izooto_show_sw_not_found_notice();
	}
}
