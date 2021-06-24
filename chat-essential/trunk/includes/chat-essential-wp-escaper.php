<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */
class WP_Escaper {

	/**
	 * @since    0.0.1
	 * @param    string               $value             The attribute that needs to be escaped
	 */
	public static function escAttr($value) {
    	if (function_exists('esc_attr')) {
      		return esc_attr($value);
    	}

		return $value;
	}

	/**
	 * @since    0.0.1
	 * @param    string               $value             The javascript that needs to be escaped
	 */
  	public static function escJS($value) {
    	if (function_exists('esc_js')) {
      		return esc_js($value);
    	}

        return $value;
	}

}
