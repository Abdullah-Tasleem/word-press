<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class WC_APF_Helpers {

	/* ----------  default plugin options ----------- */
	public static function defaults() {
		return array(
			'enabled'     => array( 'gender', 'price', 'size', 'color' ), // default ON
			'order'       => array( 'gender', 'price', 'size', 'color' ),
			'sidebar_w'   => 25, // percent
		);
	}

	/* ----------  get option ------------ */
	public static function get_option( $key ) {

		$opts = get_option( 'wc_apf_options', self::defaults() );
		return isset( $opts[ $key ] ) ? $opts[ $key ] : false;
	}

	/* ----------  update single key ------------- */
	public static function update_option( $key, $value ) {
		$opts        = get_option( 'wc_apf_options', self::defaults() );
		$opts[ $key ] = $value;
		update_option( 'wc_apf_options', $opts );
	}

	/* ----------  plugin activation -------------- */
	public static function activate() {
		if ( ! get_option( 'wc_apf_options' ) ) {
			add_option( 'wc_apf_options', self::defaults() );
		}
	}
}