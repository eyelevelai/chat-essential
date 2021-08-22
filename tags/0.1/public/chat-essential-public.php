<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/public
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Public {

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * @since    0.0.1
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chat-essential.css', array(), $this->version, 'all' );
	}

	/**
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chat-essential.js', array( 'jquery' ), $this->version, false );
	}

	public function manage_footer() {
		if (is_admin()) {
			return;
		}

		$settings = get_option(CHAT_ESSENTIAL_OPTION);
		if (empty($settings) ||
			empty($settings['apiKey'])) {
			return;
		}
		$apiKey = $settings['apiKey'];

		global $wpdb;
		global $post;

		$table_name = $wpdb->prefix . 'chat_essential';
		$rule = $wpdb->get_results( "SELECT * FROM $table_name WHERE status='active' AND api_key='$apiKey'" );
		$out = '';
		if ( ! empty( $rule ) ) {
			foreach ( $rule as $key => $data ) {
				switch ( $data->display_on ) {
					case 'all':
						$flowName = $data->flow_name;
						$chat = array(
							'origin' => 'web',
						);
						if (CHAT_ESSENTIAL_ENV !== 'prod') {
							$chat['env'] = CHAT_ESSENTIAL_ENV;
						}
						$out .= Chat_Essential_Pixel::generatePixel($apiKey, $data->flow_name, $chat);
						break;
					case 'posts':
					case 'pages':
					case 'categories':
					case 'tags':
					case 'postTypes':
				}
			}
		}
		echo $out;
	}

}
