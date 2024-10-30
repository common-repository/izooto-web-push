<?php
/**
 * This file contais method to trigger differenct woocmmerce playbook.
 *
 * @package izooto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the izooto query.
 *
 * @param string $iz_settings as param.
 * @param string $product_data as param.
 * @param string $bd_data as param.
 * @param string $iz_auth_pk as param.
 * @param string $iz_end_point as param.
 * @param string $pte as param.
 * @param string $bkey as param.
 * @param string $act as param.
 */
function izooto_build_trigger_data( $iz_settings, $product_data, $bd_data, $iz_auth_pk, $iz_end_point, $pte, $bkey, $act ) {
	if ( count( $product_data ) > 0 ) {
		$product_data = wp_json_encode( $product_data );
	} else {
		$product_data = '{}';
	}
	$curl_data = array(
		'pid'   => $iz_settings['pid'],
		'bKey'  => $bkey,
		'et'    => 'trg',
		'act'   => $act, // goal_product_viewed,goal_added_to_cart.
		'val'   => $product_data,
		'btype' => $bd_data->b_type,
		'dtype' => $bd_data->d_type,
		'pte'   => $pte,
		'auth'  => $iz_auth_pk['auth'],
		'pk'    => $iz_auth_pk['pk'],
		'ep'    => $iz_end_point,
	);
	return $curl_data;
}

/**
 * Return the izooto query.
 *
 * @param string $cart_item_key as param.
 * @param string $product_id as param.
 * @param string $quantity as param.
 * @param string $variation_id as param.
 */
function izooto_add_to_cart(
$cart_item_key,
$product_id,
$quantity,
$variation_id
) {
	if ( $product_id ) {
		include_once 'class-init.php';
		$iz_obj    = new Init();
		$data      = array(
			'product_id'   => $product_id,
			'updated_date' => gmdate( 'Y-m-d H:i:s' ),
		);
		$json_data = wp_json_encode( $data );
		$iz_obj->izooto_update_option( 'izooto-addedtocart-product-id', $json_data );
		include_once 'class-izwoocommeventshelper.php';
		$iz_events_obj  = new IzWoocommEventsHelper();
		$iz_events_flag = $iz_events_obj->get_iz_event_flag();
		$iz_token       = $iz_events_obj->izooto_get_token();
		$bd_data        = $iz_events_obj->izooto_get_bd_data();
		$pte            = $iz_events_obj->izooto_get_pte();

		if ( ( $iz_events_flag ) && ( $iz_token ) && ( $bd_data ) ) {
			$valid_token  = stripslashes( $iz_token );
			$iz_auth_pk   = $iz_events_obj->izooto_get_auth_pk( $valid_token );
			$iz_end_point = $iz_events_obj->izooto_get_endpoint( $valid_token );

			if ( ( $iz_auth_pk ) && ( $iz_end_point ) ) {
				$product_data = $iz_events_obj->izooto_get_cart_data( $product_id, $variation_id );

				if ( $product_data ) {
					$valid_token = stripslashes( $iz_token );
					$bkey        = $iz_events_obj->izooto_extract_token( $valid_token );
					include_once 'class-init.php';
					$iz_obj      = new Init();
					$iz_settings = $iz_obj->izooto_get_option( 'izooto-settings' );

					$curl_data        = izooto_build_trigger_data( $iz_settings, array(), $bd_data, $iz_auth_pk, $iz_end_point, $pte, $bkey, 'product_browsed' );
					$iz_curl_response = $iz_events_obj->izooto_curl_request( $curl_data );

					$curl_data        = izooto_build_trigger_data( $iz_settings, array(), $bd_data, $iz_auth_pk, $iz_end_point, $pte, $bkey, 'product_addedtocart' );
					$iz_curl_response = $iz_events_obj->izooto_curl_request( $curl_data );

					$curl_data = izooto_build_trigger_data( $iz_settings, $product_data, $bd_data, $iz_auth_pk, $iz_end_point, $pte, $bkey, 'added_to_cart' );

					$iz_curl_response = $iz_events_obj->izooto_curl_request( $curl_data );

				}
			}
		}
	}
}

/**
 * Hit izooto api on place order.
 *
 * @param string $order_id as param.
 */
function izooto_order_place( $order_id ) {
	if ( $order_id ) {
		include_once 'class-izwoocommeventshelper.php';
		$iz_events_obj  = new IzWoocommEventsHelper();
		$iz_events_flag = $iz_events_obj->get_iz_event_flag();
		$iz_token       = $iz_events_obj->izooto_get_token();
		$bd_data        = $iz_events_obj->izooto_get_bd_data();
		$pte            = $iz_events_obj->izooto_get_pte();

		if ( ( $iz_events_flag ) && ( $iz_token ) && ( $bd_data ) ) {
			$valid_token  = stripslashes( $iz_token );
			$iz_auth_pk   = $iz_events_obj->izooto_get_auth_pk( $valid_token );
			$iz_end_point = $iz_events_obj->izooto_get_endpoint( $valid_token );

			if ( ( $iz_auth_pk ) && ( $iz_end_point ) ) {
				$order_data = $iz_events_obj->izooto_get_orders_data( $order_id );

				if ( $order_data ) {
					$valid_token = stripslashes( $iz_token );
					$bkey        = $iz_events_obj->izooto_extract_token( $valid_token );
					include_once 'class-init.php';
					$iz_obj           = new Init();
					$iz_settings      = $iz_obj->izooto_get_option( 'izooto-settings' );
					$curl_data        = izooto_build_trigger_data( $iz_settings, $order_data, $bd_data, $iz_auth_pk, $iz_end_point, $pte, $bkey, 'order_placed' );
					$iz_curl_response = $iz_events_obj->izooto_curl_request( $curl_data );

				}
			}
		}
	}
}

/**
 * Hit izooot api on product view
 */
function izooto_product_view() {
	global $product;
	$product_id = $product->get_id();
	if ( $product_id ) {
		if ( ! isset( $_REQUEST['utm_source'] ) ) {
			include_once 'class-izwoocommeventshelper.php';
			$iz_events_obj  = new IzWoocommEventsHelper();
			$iz_events_flag = $iz_events_obj->get_iz_event_flag();
			$iz_token       = $iz_events_obj->izooto_get_token();
			$bd_data        = $iz_events_obj->izooto_get_bd_data();
			$pte            = $iz_events_obj->izooto_get_pte();

			if ( ( $iz_events_flag ) && ( $iz_token ) && ( $bd_data ) ) {
				$valid_token  = stripslashes( $iz_token );
				$iz_auth_pk   = $iz_events_obj->izooto_get_auth_pk( $valid_token );
				$iz_end_point = $iz_events_obj->izooto_get_endpoint( $valid_token );

				if ( ( $iz_auth_pk ) && ( $iz_end_point ) ) {
					$product_data = $iz_events_obj->izooto_product_data( $product_id );

					if ( $product_data ) {
						$bkey = $iz_events_obj->izooto_extract_token( $valid_token );
						include_once 'class-init.php';
						$iz_obj          = new Init();
						$iz_settings     = $iz_obj->izooto_get_option( 'izooto-settings' );
						$izjson_data     = $iz_obj->izooto_get_option( 'izooto-addedtocart-product-id' );
						$izcartproductid = json_decode( $izjson_data, 1 )['product_id'];
						$izprodaddtime   = json_decode( $izjson_data, 1 )['updated_date'];
						$camparedate     = gmdate( 'Y-m-d H:i:s', strtotime( $izprodaddtime . ' +2 minutes' ) );
						$data            = array(
							'product_id'   => $product_id,
							'updated_date' => $camparedate,
						);
						$json_data       = wp_json_encode( $data );

						if ( $izcartproductid !== $product_id || ( $izcartproductid === $product_id && $camparedate < gmdate( 'Y-m-d H:i:s' ) ) ) {
							$curl_data        = izooto_build_trigger_data( $iz_settings, array(), $bd_data, $iz_auth_pk, $iz_end_point, $pte, $bkey, 'product_browsed' );
							$iz_curl_response = $iz_events_obj->izooto_curl_request( $curl_data );
							$curl_data        = izooto_build_trigger_data( $iz_settings, $product_data, $bd_data, $iz_auth_pk, $iz_end_point, $pte, $bkey, 'product_viewed' );
							$iz_curl_response = $iz_events_obj->izooto_curl_request( $curl_data );

						}
					}
				}
			}
		}
	}
}

/**
 * Hit izooot api on category view
 */
function izooto_category_view() {
	if ( is_product_category() ) {
		if ( ! isset( $_REQUEST['utm_source'] ) ) {
			include_once 'class-izwoocommeventshelper.php';
			$iz_events_obj  = new IzWoocommEventsHelper();
			$iz_events_flag = $iz_events_obj->get_iz_event_flag();
			$iz_token       = $iz_events_obj->izooto_get_token();
			$bd_data        = $iz_events_obj->izooto_get_bd_data();
			$pte            = $iz_events_obj->izooto_get_pte();

			if ( ( $iz_events_flag ) && ( $iz_token ) && ( $bd_data ) ) {
				$valid_token  = stripslashes( $iz_token );
				$iz_auth_pk   = $iz_events_obj->izooto_get_auth_pk( $valid_token );
				$iz_end_point = $iz_events_obj->izooto_get_endpoint( $valid_token );

				if ( ( $iz_auth_pk ) && ( $iz_end_point ) ) {
					global $wp_query;
					$category      = $wp_query->get_queried_object();
					$category_data = $iz_events_obj->izooto_get_category_data( $category );
					$bkey          = $iz_events_obj->izooto_extract_token( $valid_token );

					include_once 'class-init.php';
					$iz_obj           = new Init();
					$iz_settings      = $iz_obj->izooto_get_option( 'izooto-settings' );
					$curl_data        = izooto_build_trigger_data( $iz_settings, $category_data, $bd_data, $iz_auth_pk, $iz_end_point, $pte, $bkey, 'collection_viewed' );
					$iz_curl_response = $iz_events_obj->izooto_curl_request( $curl_data );

				}
			}
		}
	}
}

/* Add to Cart Hook */
add_action( 'woocommerce_add_to_cart', 'izooto_add_to_cart', 10, 4 );

/* Order Placed Hook */
add_action( 'woocommerce_thankyou', 'izooto_order_place', 10, 1 );

/* Product View Hook */
add_action( 'woocommerce_after_single_product', 'izooto_product_view' );

/* Category View Hook */
add_action( 'woocommerce_after_main_content', 'izooto_category_view' );
