<?php
/**
 * Woocommerce event helper class provide the helper method which is required by main file.
 * file ordering matters
 *
 * @package izooto
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Cotains all helping function to extract subscription & woocommerce helper method
 * such as detect single page & add to cart page
 */
class IzWoocommEventsHelper {
	/**
	 * Flag based config to pass whether to track event or not
	 *
	 * @var evt_trk event tracking
	 */
	public $evt_trk;
	/**
	 * Push token object
	 *
	 * @var push_token event tracking
	 */
	public $push_token;
	/**
	 * Izooto uid
	 *
	 * @var izooto_uid izooto uid
	 */
	public $izooto_uid;
	/**
	 * Izooto browser type
	 *
	 * @var b_type izooto uid
	 */
	public $b_type;
	/**
	 * Izooto browser type
	 *
	 * @var d_type izooto uid
	 */
	public $d_type;

	/**
	 * Constructor read dynamic value from izootoWPConfig cookie which is set by izooto's sdk
	 */
	public function __construct() {
		$this->izooto_uid = false;
		$this->push_token = false;
		$this->d_type     = false;
		$this->b_type     = false;
		$this->evt_trk    = false;
		if ( isset( $_COOKIE['izootoWpConfig'] ) ) {
			$sdk_cookie = urldecode( izooto_get_cookie_data( 'izootoWpConfig' ) );
			$sdk_cookie = json_decode( $sdk_cookie );
			if ( isset( $sdk_cookie->evt_trk ) ) {
				$this->evt_trk = $sdk_cookie->evt_trk;
			}

			if ( isset( $sdk_cookie->push_token ) ) {
				$this->push_token = $sdk_cookie->push_token;
			}

			if ( isset( $sdk_cookie->izooto_uid ) ) {
				$this->izooto_uid = $sdk_cookie->izooto_uid;
			}

			if ( isset( $sdk_cookie->b_type ) ) {
				$this->b_type = $sdk_cookie->b_type;
			}

			if ( isset( $sdk_cookie->d_type ) ) {
				$this->d_type = $sdk_cookie->d_type;
			}
		}
	}

	/**
	 * Get Izooto Event Flag
	 */
	public function get_iz_event_flag() {
		return $this->evt_trk;
	}

	/**
	 * Uid which is set by cookie
	 */
	public function izooto_get_token() {
		return $this->izooto_uid;
	}

	/**
	 * Get Iz bddata
	 */
	public function izooto_get_bd_data() {
		if ( $this->b_type && $this->d_type ) {
			$object         = new stdClass();
			$object->b_type = $this->b_type;
			$object->d_type = $this->d_type;
			return $object;
		}
		return false;
	}

	/**
	 * Extract auth & pk
	 *
	 * @param string $iz_token as param.
	 */
	public function izooto_get_auth_pk( $iz_token ) {
		if ( $this->push_token ) {
				$iz_auth_pk = array(
					'pk'   => $this->push_token->keys->p256dh,
					'auth' => $this->push_token->keys->auth,
				);
				return $iz_auth_pk;
		}
		return false;
	}

	/**
	 * Extract endpoint
	 *
	 * @param string $iz_token as param.
	 */
	public function izooto_get_endpoint( $iz_token ) {
		if ( $this->push_token->endpoint ) {
			return $this->push_token->endpoint;
		}
		return false;
	}

	/**
	 * Return pte value by defualt 2
	 */
	public function izooto_get_pte() {
		$iz_pte = 2;
		if ( isset( $_COOKIE['izpte'] ) ) {
			$iz_pte = sanitize_text_field( wp_unslash( $_COOKIE['izpte'] ) );
		}
		return $iz_pte;
	}

	/**
	 * Return product detail by product id
	 *
	 * @param string $product_id as param.
	 */
	public function izooto_get_product_detail_by_id( $product_id ) {
		return wc_get_product( $product_id );
	}

	/**
	 * Return token
	 *
	 * @param string $token  as parameter.
	 */
	public function izooto_extract_token( $token ) {
		return $token;
	}

	/**
	 * Get Product Data
	 *
	 * @param string $product_id  as parameter.
	 */
	public function izooto_product_data( $product_id ) {
		$product_data = $this->izooto_get_product_detail_by_id( $product_id );
		if ( $product_data ) {
			$product_array = array(
				'product_name' => trim( $product_data->get_name() ),
			);

			if ( $product_data->get_price() ) {
				$product_array['product_price'] = $product_data->get_price();
			}

			if ( $product_data->get_image_id() ) {
				$image_array = wp_get_attachment_image_src( $product_data->get_image_id(), 'full' );
				if ( $image_array ) {
					$product_array['product_image'] = trim( $image_array[0] );
				}
			}

			if ( ! isset( $product_array['product_image'] ) ) {
				$product_array['product_image'] = wc_placeholder_img_src( 'full' );
			}

			if ( $product_data->get_description() ) {
				$product_array['description'] = trim( $product_data->get_description() );
			}

			$product_array['product_url'] = trim( get_permalink( $product_id ) );

			return $product_array;
		}
		return false;
	}

	/**
	 * Izooto Curl Request
	 *
	 * @param string $params as parameter.
	 * @param string $url to trigger.
	 */
	public function izooto_curl_request( $params, $url = 'https://trg.izooto.com/trg' ) {
		global $wp;
		if ( isset( $wp->query_vars ) ) {
			$referer = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
		} else {
			$referer = home_url( $wp->request );
		}
		$curl_array = array(
			'headers' => array(
				'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
				'User-Agent'   => sanitize_text_field( wp_unslash( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' ) ),
				'Referer'      => $referer,
			),
			'body'    => $params,
		);

		$response = wp_remote_post( $url, $curl_array );
		return $response;
	}

	/**
	 * Get Order Data By ID
	 *
	 * @param string $order_id as parameter.
	 */
	public function izooto_get_order_data_by_id( $order_id ) {
		return wc_get_order( $order_id );
	}

	/**
	 * Get Order Data
	 *
	 * @param string $order_id as parameter.
	 */
	public function izooto_get_orders_data( $order_id ) {
		$order_object = $this->izooto_get_order_data_by_id( $order_id );
		if ( $order_object ) {
			$order_data_array = array(
				'id'             => strval( $order_id ),
				'customer_email' => $order_object->get_billing_email(),
			);

			$order_data_array['cart_value']    = $order_object->get_total();
			$order_data_array['product_count'] = strval( count( $order_object->get_items() ) );

			foreach ( array_reverse( $order_object->get_items() ) as $key => $value ) {
				$product                           = $value->get_product();
				$order_data_array['product_name']  = trim( $value->get_name() );
				$order_data_array['product_price'] = $value->get_total();
				$order_data_array['qty_ordered']   = strval( $value->get_quantity() );
				if ( $product->get_image_id() ) {
					$image_array = wp_get_attachment_image_src( $product->get_image_id(), 'full' );
					if ( $image_array ) {
						$order_data_array['product_image'] = trim( $image_array[0] );
					}
				}

				if ( ! isset( $order_data_array['product_image'] ) ) {
					$order_data_array['product_image'] = wc_placeholder_img_src( 'full' );
				}
				return $order_data_array;
			}
		}
		return false;
	}

	/**
	 * Get Cart Data
	 *
	 * @param string $product_id as parameter.
	 * @param string $variation_id as parameter.
	 */
	public function izooto_get_cart_data( $product_id, $variation_id ) {
		if ( WC()->cart->get_cart_contents_count() ) {
			foreach ( WC()->cart->get_cart() as $key => $value ) {

				if ( ( $product_id === $value['product_id'] ) && ( $variation_id === $value['variation_id'] ) ) {
					$data          = $value['data'];
					$product_array = array(
						'product_name'  => trim( $data->get_name() ),
						'product_price' => $data->get_price(),
					);

					$product_array['cart_url'] = trim( get_permalink( $product_id ) );
					$product_array['qty']      = strval( $value['quantity'] );
					if ( $data->get_image_id() ) {
						$image_array = wp_get_attachment_image_src( $data->get_image_id(), 'full' );
						if ( $image_array ) {
							$product_array['product_image'] = trim( $image_array[0] );
						}
					}

					if ( ! isset( $product_array['product_image'] ) ) {
						$product_array['product_image'] = wc_placeholder_img_src( 'full' );
					}

					if ( $data->get_description() ) {
						$product_array['description'] = trim( $data->get_description() );
					}

					return $product_array;
				}
			}
		}
		return false;
	}

	/**
	 * Get Category Data
	 *
	 * @param string $category as parameter.
	 */
	public function izooto_get_category_data( $category ) {
		$category_array = array(
			'collection_name' => $category->name,
			'collection_url'  => get_category_link( $category ),
		);
		$thumbnail_id   = get_term_meta( $category->term_id, 'thumbnail_id', true );
		if ( $thumbnail_id ) {
			$image = wp_get_attachment_url( $thumbnail_id );
			if ( $image ) {
				$category_array['collection_image'] = $image;
			}
		}

		if ( $category->description ) {
			$category_array['description'] = $category->description;
		}

		if ( $category->count ) {
			$category_array['product_count'] = strval( $category->count );
		}
		return $category_array;
	}

}
