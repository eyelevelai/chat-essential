<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_i18n {


	/**
	 * @since    0.0.1
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'chat-essential',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
