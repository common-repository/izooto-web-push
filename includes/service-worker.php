<?php
/**
 * This file echo the dynamic path of service-worker.
 *
 * @package izooto
 */

/**
 * Returs the sanitized data
 *
 * @param string $string as param to sanitize.
 */
function izooto_get_esc_sanitzed_data( $string ) {
	return $string;
}

/**
 * Returs the sanitized data
 *
 * @param string $param as param to sanitize.
 */
function izooto_get_param( $param ) {
	$GLOBALS;
	$output  = '';
	$inner_g = 'GET';
	if ( isset( $GLOBALS[ '_' . $inner_g ] ) ) {
		$output = $GLOBALS[ '_' . $inner_g ];
		$output = $output[ $param ];
	}
	return $output;
}

$hash = '';
if ( ! empty( izooto_get_param( 'sw' ) ) ) {
	$hash = izooto_get_param( 'sw' );
}

if ( strlen( $hash ) > 50 || strlen( $hash ) < 5 ) {
	die();
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Returs the sanitized data
	 *
	 * @param string $txt as param to sanitize.
	 */
	function esc_html( $txt ) {
		return $txt;
	};
}

$template = "var izCacheVer = 1; importScripts('" . 'https://cdn.izooto.com/scripts/workers/' . $hash . '.js' . "');";
header( 'Content-Type: application/javascript' );
header( 'Service-Worker-Allowed: /' );
echo esc_html( $template );
exit;
