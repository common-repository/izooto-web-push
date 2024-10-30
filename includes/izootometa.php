<?php
/**
 * This file contais the helper method to create izooto ui in post page & send motification.
 * file ordering matters
 *
 * @package izooto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display notices for version smaller than 5.0.
 */
function admin_message_classic_editor() {
	if ( isset( $_COOKIE['izmessage'] ) ) {
		$iz_message_cookie = izooto_get_cookie_data( 'izmessage' );
		add_action(
			'admin_notices',
			function() use ( $iz_message_cookie ) {
				if ( 1 == $iz_message_cookie ) {
					$class   = 'success';
					$message = esc_html( 'iZooto: Notification has been pushed successfully.' );
				} elseif ( 3 == $iz_message_cookie ) {
					$class   = 'error';
					$message = esc_html( 'iZooto: Unable to process notification push request. Contact support@izooto.com.' );
				} elseif ( 4 == $iz_message_cookie ) {
					$class   = 'error';
					$message = esc_html( 'iZooto: Daily Campaign Push Limit exceeded for the day.' );
				} elseif ( 5 == $iz_message_cookie ) {
					$class   = 'error';
					$message = esc_html( "iZooto: Both 'Title' and 'Message' are mandatory fields." );
				} elseif ( 2 == $iz_message_cookie ) {
					$class   = 'error';
					$message = esc_html( 'iZooto: There is an issue with the plugin. Please contact iZooto support for help.' );
				}
				?>

				<?php
				if ( ( isset( $class ) ) && ( isset( $message ) ) ) {
					?>
			<div class="notice notice-<?php echo esc_html( $class ); ?> is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
					<?php
				}
			}
		);
	}
}

/**
 * Add custom box
 */
function izooto_add_custom_box() {
	add_meta_box(
		'izooto_notify',
		'iZooto push notifications',
		'izooto_notify_html',
		'post',
		'side',
		'high'
	);
}

/**
 * Notify methood to send push notification
 *
 * @param string $post as param.
 */
function izooto_notify_html( $post ) {

	/* Get users current screen */
	$current_screen = get_current_screen();
	$screen         = 0;
	include_once 'class-init.php';
	$iz_obj = new Init();
	if ( ! empty( $current_screen ) ) {
		if ( method_exists( $current_screen, 'is_block_editor' ) ) {
			if ( $current_screen->is_block_editor() ) {
				$editor_script = 'iznotify_editor_script';
				wp_enqueue_script( 'cookies' );
				$screen = 1;
			} else {
				$editor_script = 'iznotify_script';
			}
		} else {
			$editor_script = 'iznotify_script';
		}

		if ( isset( $editor_script ) ) {
			$iz_site_name = $iz_obj->izooto_get_option( 'izooto-site-name' );
			if ( empty( $iz_site_name ) ) {
				$iz_site_name = get_bloginfo( 'name' );
			}
			wp_enqueue_script( $editor_script );
			wp_localize_script( $editor_script, 'site_name_param', array( 'site_name' => $iz_site_name ) );
		}
	}
	wp_enqueue_style( 'iznotify_style' );

	wp_nonce_field( basename( __FILE__ ), 'izooto_notify_opt_nonce' );
	wp_nonce_field( basename( __FILE__ ), 'iz_site_title_nonce' );
	$iz_settings = $iz_obj->izooto_get_option( 'izooto-settings' );

	$site_title_checked  = '';
	$iz_site_title       = '';
	$site_title_settings = $iz_obj->izooto_get_option( 'izooto-site-name-settings' );
	if ( ! empty( $site_title_settings ) ) {
		$site_title_checked = 'checked';
		$iz_site_title      = $iz_obj->izooto_get_option( 'izooto-site-name' );
	}

	/* Display notifications sent and maximum limit */
	if ( ! empty( $iz_settings['notify_count_date'] ) ) {
		if ( strtotime( $iz_settings['notify_count_date']['current_date'] ) === strtotime( current_time( 'Y-m-d' ) ) ) {
			?>
	<p>Notifications sent today: <?php echo esc_html( $iz_settings['notify_count_date']['notify_count'] ); ?></p>
	<p>Maximum notifications per day: <?php echo esc_html( $iz_settings['notify_count_date']['max_notify_count'] ); ?></p>
			<?php
		}
	}
	?>

	<input type="checkbox" name="izooto_notify_opt" id="izooto_notify_opt">
	<?php
	if ( 'publish' === $post->post_status ) {
		?>
	<label for="izooto_notify_opt">Send notification on post update</label>
		<?php
	} else {
		?>
	<label for="izooto_notify_opt">Send notification on post publish</label>
		<?php
	}
	?>

	<br/ ><br/ >

	<input type="checkbox" name="iz_site_title" id="iz_site_title" <?php echo esc_html( $site_title_checked ); ?>>
	<label for="izooto_notify_opt">Use site and post title as notification title and message, respectively.</label><br/ ><br/ >

	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="svg_icon_title">
				<label for="iz_notify_title">Title</label>
				<div class="tooltip_svg_icon">
					<svg style="margin-left: 3px;margin-top: 3px;" width="14" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><defs><style>.cls-1{fill:none;}.cls-2{fill:#fffdfd;}</style></defs><path class="cls-1" d="M-1.72-1.67h24v24h-24Z" transform="translate(1.72 1.67)"/><circle cx="12" cy="12" r="10"/><path class="cls-2" d="M10.28,17.86a1.61,1.61,0,0,1-1.6-1.61V9.83a1.61,1.61,0,1,1,3.21,0v6.42A1.62,1.62,0,0,1,10.28,17.86Z" transform="translate(1.72 1.67)"/><circle class="cls-2" cx="12" cy="6.1" r="1.71"/></svg><span class="tooltiptext">Site title will be used as the notification title, till the notification title is edited.</span>
				</div>
			</div>
			<input type="text" name="iz_notify_title" id="iz_notify_title" class="iz-form-control" value="<?php echo esc_html( $iz_site_title ); ?>">
			<div class="row" id="iz_site_title_row" style="display: none;">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="site-title-msg">Site title does not exist.</div>
				</div>
			</div>
			<span id="iz_title_limit"></span>
		</div>
	</div> <br/ >

	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<label for="iz_notify_content">Message</label>
			<textarea name="iz_notify_content" id="iz_notify_content" class="iz-form-control iz-form-textarea" rows="3"></textarea>
			<span id="iz_content_limit"></span>
		</div>
	</div>

	<?php
	if ( $screen ) {
		?>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="save_custom_content_div" style="display: none;">
				<button type="submit" id="save_custom_content" class="custom_content_save">Save</button>
			</div>
		</div>
		<?php
	}

}
/**
 * Create notification table in db
 */
function izooto_create_notification_tbl() {
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
 * Send push notification
 *
 * @param number $post_id as param.
 * @param string $post as param.
 * @param string $iz_notify_title as param.
 * @param string $iz_notify_content as param.
 */
function izooto_notification_send( $post_id, $post, $iz_notify_title, $iz_notify_content ) {

		include_once 'class-init.php';
		$iz_obj             = new Init();
		$iz_settings        = $iz_obj->izooto_get_option( 'izooto-settings' );
		$limit_notification = 1;

		/* Check if users max limit reached */
	if ( ! empty( $iz_settings['notify_count_date'] ) ) {
		if ( strtotime( $iz_settings['notify_count_date']['current_date'] ) === strtotime( current_time( 'Y-m-d' ) ) ) {
			if ( $iz_settings['notify_count_date']['notify_count'] >= $iz_settings['notify_count_date']['max_notify_count'] ) {
				$limit_notification = 0;
				setcookie( 'izmessage', 4, time() + 10, '/' );
			}
		}
	}

	if ( $limit_notification ) {

		$iz_notify_title = str_replace( '\"', '"', trim( $iz_notify_title ) );
		$iz_notify_title = str_replace( "\'", "'", $iz_notify_title );

		$iz_notify_content = str_replace( '\"', '"', trim( $iz_notify_content ) );
		$iz_notify_content = str_replace( "\'", "'", $iz_notify_content );

		$post_data = array(
			'platform'     => 1,
			'token'        => $iz_settings['token'],
			'pid'          => $iz_settings['pid'],
			'title'        => $iz_notify_title,
			'message'      => $iz_notify_content,
			'landing_url'  => get_permalink( $post ),
			'utm_source'   => 'izooto',
			'utm_medium'   => 'push_notification',
			'utm_campaign' => rawurlencode( str_replace( ' ', '_', $iz_notify_title ) ),
			'ttl'          => 86400,
			'target'       => array(
				'type' => 'all',
			),
		);

		$banner_url = get_the_post_thumbnail_url( $post_id );
		if ( ! empty( $banner_url ) ) {
			$post_data['banner_url'] = trim( $banner_url );
		}

		$post_array = array(
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			),
			'body'    => $post_data,
		);

		/* Api request for notification */
		$notification_request = izooto_curl_request( $post_array );

		/* Save site title */
		if ( isset( $_POST['iz_site_title_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['iz_site_title_nonce'] ), basename( __FILE__ ) ) ) {
			if ( isset( $_POST['iz_site_title'] ) && sanitize_key( $_POST['iz_site_title'] ) ) {
				$iz_obj->izooto_update_option( 'izooto-site-name', $iz_notify_title );
				$iz_obj->izooto_update_option( 'izooto-site-name-settings', 1 );
			} else {
				$iz_obj->izooto_update_option( 'izooto-site-name-settings', 0 );
			}
		}

		if ( ( ! empty( $notification_request ) ) && ( ! is_wp_error( $notification_request ) ) ) {
			$response_body = json_decode( $notification_request['body'] );

			if ( property_exists( $response_body, 'notification_id' ) ) {
				if ( property_exists( $response_body, 'notification_count' ) ) {

					/* Get iZooto settings */
					$izooto_op_new                         = izooto_settings_update( $iz_settings );
					$notify_count_date                     = array();
					$notify_count_date['current_date']     = current_time( 'Y-m-d' );
					$notify_count_date['notify_count']     = $response_body->notification_count->notify_count;
					$notify_count_date['max_notify_count'] = $response_body->notification_count->max_notify_count;
					$izooto_op_new['notify_count_date']    = $notify_count_date;
					$iz_obj->izooto_update_option( 'izooto-settings', $izooto_op_new );
				}
				setcookie( 'izmessage', 1, time() + 10, '/' );
			} elseif ( 'Daily campaign limit exceeded' === $response_body->message ) {
					setcookie( 'izmessage', 4, time() + 10, '/' );
			} elseif ( property_exists( $response_body, 'success' ) ) {
				if ( ! $response_body->success ) {
					setcookie( 'izmessage', 3, time() + 10, '/' );
				}
			}
		} else {
			$error_array = array(
				'pid'     => $iz_settings['pid'],
				'message' => $notification_request->get_error_message(),
			);
			$post_array  = array(
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => $error_array,
			);
			izooto_log_error( $post_array );
			setcookie( 'izmessage', 2, time() + 10, '/' );
		}
	}

}

/**
 * Save post request data
 *
 * @param number $post_id as param.
 * @param string $post as param.
 */
function izooto_save_postdata( $post_id, $post ) {

	$error_array            = array(
		'pid'     => $post_id,
		'message' => $post->post_status,
	);
				$post_array = array(
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'body'    => array( 'pid' => wp_json_encode( $error_array ) ),
				);

				if ( ( isset( $post_id ) ) && ( isset( $post ) ) ) {
					$post_status = array(
						'publish',
						'future',
					);
					if ( ( 'post' === $post->post_type ) && ( in_array( $post->post_status, $post_status, true ) ) ) {

						if ( isset( $_POST['izooto_notify_opt_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['izooto_notify_opt_nonce'] ), basename( __FILE__ ) ) ) {
							if ( ! empty( $_POST['izooto_notify_opt'] ) ) {
								if ( ( ! empty( $_POST['iz_notify_title'] ) ) && ( ! empty( $_POST['iz_notify_content'] ) ) ) {
									$iz_notify_title   = sanitize_text_field( wp_unslash( $_POST['iz_notify_title'] ) );
									$iz_notify_content = sanitize_text_field( wp_unslash( $_POST['iz_notify_content'] ) );
									$iz_notify_content = strip_shortcodes( $iz_notify_content );
									if ( 'publish' === $post->post_status ) {
										izooto_notification_send( $post_id, $post, $iz_notify_title, $iz_notify_content );
									} elseif ( 'future' === $post->post_status ) {
										$banner_url = get_the_post_thumbnail_url( $post_id );
										if ( ! empty( $banner_url ) ) {
											$banner_url = trim( $banner_url );
										}
										izooto_create_notification_tbl();
										global $wpdb;
										$table_name              = $wpdb->prefix . 'iz_notifications_onpush';
										$store_arr['post_id']    = $post_id;
										$store_arr['title']      = $iz_notify_title;
										$store_arr['message']    = $iz_notify_content;
										$store_arr['banner_url'] = $banner_url;
										$wpdb->insert( $table_name, $store_arr );

									}
								} else {
									setcookie( 'izmessage', 5, time() + 10, '/' );
								}
							}
						}
					}
				}
}

/**
 * Do curl request
 *
 * @param array  $array as param.
 * @param string $url as param.
 */
function izooto_curl_request( $array, $url = 'https://middlewarev3.izooto.com/wordpress/notification-push' ) {
	$response = wp_remote_post( $url, $array );
	return $response;
}

/**
 * Log error to izooto api
 *
 * @param array  $array as param.
 * @param string $url as param.
 */
function izooto_log_error( $array, $url = 'https://middlewarev3.izooto.com/wordpress/wp-log-error' ) {
	$response = wp_remote_post( $url, $array );
	return $response;
}

/**
 * Return izooto settion to update the options
 *
 * @param array $settings as param.
 */
function izooto_settings_update( $settings ) {
	$izooto_op          = array();
	$izooto_op['url']   = $settings['url'];
	$izooto_op['uid']   = $settings['uid'];
	$izooto_op['pid']   = $settings['pid'];
	$izooto_op['cdn']   = $settings['cdn'];
	$izooto_op['sw']    = $settings['sw'];
	$izooto_op['gcm']   = $settings['gcm'];
	$izooto_op['token'] = $settings['token'];

	if ( isset( $settings['wcom'] ) ) {
		$izooto_op['wcom'] = $settings['wcom'];
	} else {
		$izooto_op['wcom'] = 0;
	}

	return $izooto_op;
}

/* Add meta boxes */
add_action( 'add_meta_boxes', 'izooto_add_custom_box' );

/* Save post action */
add_action( 'save_post', 'izooto_save_postdata', 10, 2 );

add_action( 'admin_init', 'admin_message_classic_editor' );


add_action( 'transition_post_status', 'izooto_send_new_post', 10, 3 );

/**
 * Listen for publishing of a new post
 *
 * @param string $new_status as param.
 * @param string $old_status as param.
 * @param string $post as param.
 */
function izooto_send_new_post( $new_status, $old_status, $post ) {
	try {
		if ( 'publish' === $new_status && 'publish' !== $old_status && 'post' === $post->post_type ) {
			$post_id = $post->ID;
			izooto_create_notification_tbl();
			global $wpdb;
			$table_name = $wpdb->prefix . 'iz_notifications_onpush';
			global $wpdb;

			$results = wp_cache_get( $post_id, 'iz_notifications_onpush' );
			if ( ! $results ) {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
						'SELECT * FROM %1s WHERE post_id=%d',
						$table_name,
						$post_id
					),
					ARRAY_A
				);
				wp_cache_add( $post_id, $results, 'iz_notifications_onpush' );
			}

			$rowcount = $wpdb->num_rows;
			if ( $rowcount > 0 ) {
				$title = '';
				if ( isset( $results[0]->title ) ) {
					$title = $results[0]->title;
				} else {
					$title = $results[0]['title'];
				}
				$message = '';
				if ( isset( $results[0]->message ) ) {
					$message = $results[0]->message;
				} else {
					$message = $results[0]['message'];
				}
				$error_array    = array(
					'pid'   => $post->ID,
					'title' => $title,
				);
					$post_array = array(
						'headers' => array(
							'Content-Type' => 'application/x-www-form-urlencoded',
						),
						'body'    => $error_array,
					);
					izooto_notification_send( $post->ID, $post, $title, $message );
			} else {
				$error_array    = array( 'pid' => $post->ID );
					$post_array = array(
						'headers' => array(
							'Content-Type' => 'application/x-www-form-urlencoded',
						),
						'body'    => $error_array,
					);
			}
		}
	} catch ( Exception $e ) {
		$t = 0;
	}
}
