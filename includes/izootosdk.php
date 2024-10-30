<?php
/**
 * This file contais method to handle izooto SDK part.
 *
 * @package izooto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the izooto query.
 *
 * @param string $query as param.
 */
function izooto_query( $query ) {
		$query[] = 'izooto';
		return $query;
}

/**
 * Set worker url.
 */
function izooto_sdk() {
		$base_url   = get_site_url() . '/';
		$rel_url    = wp_make_link_relative( $base_url );
		$worker_url = $rel_url . '?izooto=sw';
}

/**
 * Included the izooto sdk in every page
 */
function include_izooto_sdk() {
	include_once 'class-init.php';
	$obj        = new Init();
	$opfunction = $obj->izooto_get_option( 'izooto-settings' );
	$base_url   = get_site_url();
	$rel_url    = wp_make_link_relative( $base_url );
	$https      = esc_attr( filter_input( INPUT_SERVER, 'HTTPS' ) );
	$https      = ( isset( $https ) && '' !== $https ) ? true : false;
	$safe_url   = ( substr( $base_url, 0, 8 ) === 'https://' ) ? true : false;

	if ( '' !== $opfunction['cdn'] && ! empty( $opfunction['cdn'] ) ) {
		$sdkurl = esc_url_raw( 'https://' . $opfunction['cdn'] );
		if ( ( $https || $safe_url ) ) {
			echo "<script type='text/javascript'>\n";
			echo "window.is_wp=1;\n";
			echo "window._izootoModule= window._izootoModule || {};\n";
			$path = ABSPATH . 'service-worker.js';
			if ( file_exists( $path ) ) {
				echo "window._izootoModule['swPath'] = \"" . esc_url_raw( get_site_url() ) . "/service-worker.js\";\n";
			} else {
				echo "window._izootoModule['swPath'] = \"" . esc_url_raw( IZOOTO_BASE_URL ) . 'includes/service-worker.php?sw=' . esc_html( sha1( $opfunction['pid'] ) ) . "\";\n";
			}
			echo "</script>\n";
		}
		?>
	<script> window._izq = window._izq || []; window._izq.push(["init"]);</script>
		<?php wp_enqueue_script( 'izootoWP', esc_url_raw( $sdkurl ), array(), IZVERSION, true ); ?>
		<?php
	}

}

/**
 * Expose the izooto sdk helper file on specific url
 *
 * @param string $query as param.
 */
function izooto_sdk_files( $query ) {
	include_once 'class-init.php';
	$obj    = new Init();
	$izooto = '';
	if ( isset( $query->query_vars['izooto'] ) ) {
		$izooto = $query->query_vars['izooto'];
	}
	$opfunction = $obj->izooto_get_option( 'izooto-settings' );

	if ( '' === $opfunction['token'] ) {
		return;
	}
	$template             = array(
		'manifest' => '',
		'sw'       => '',
	);
	$template['manifest'] = '{"gcm_sender_id": ' . wp_json_encode( $opfunction['gcm'] ) . '}';
	$template['sw']       = "var izCacheVer = 1; importScripts('" . esc_url_raw( 'https://' . $opfunction['sw'] ) . "');";

	if ( 'sw' === $izooto ) {
		header( 'Content-Type: application/javascript' );
		header( 'Service-Worker-Allowed: /' );
		// phpcs:ignore WordPress
		echo $template['sw'];
		exit;
	}
	if ( 'manifest' === $izooto ) {
		header( 'Content-Type: application/javascript' );
		// phpcs:ignore WordPress
		echo $template['manifest'];
		exit;
	}
}

add_filter( 'query_vars', 'izooto_query', 10, 1 );
add_action( 'wp_head', 'include_izooto_sdk' );
add_action( 'wp_footer', 'izooto_sdk' );
add_action( 'parse_request', 'izooto_sdk_files' );

?>
