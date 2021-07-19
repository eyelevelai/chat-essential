<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	/**
	 * @since    0.0.1
	 */
	public function add_menu() {
		add_menu_page(
			__('Chat Essential', 'chat-essential'),
			__('Chat Essential', 'chat-essential'),
			'manage_options',
			'chat-essential',
			array( $this, 'menu_main_page' ),
			plugin_dir_url(__FILE__) . '../images/qr-icon-gray.png',
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Settings', 'chat-essential'),
			__('Settings', 'chat-essential'),
			'manage_options',
			'chat-essential',
			array( $this, 'menu_main_page' ),
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Website Settings', 'chat-essential'),
			__('Website', 'chat-essential'),
			'manage_options',
			'chat-essential-website',
			array( $this, 'menu_main_page' ),
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Facebook Page Settings', 'chat-essential'),
			__('Facebook Page', 'chat-essential'),
			'manage_options',
			'chat-essential-fb-page',
			array( $this, 'menu_main_page' ),
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - QR Codes Settings', 'chat-essential'),
			__('QR Codes', 'chat-essential'),
			'manage_options',
			'chat-essential-qr-codes',
			array( $this, 'menu_main_page' ),
			20
		);
	}

	/**
	 * @since    0.0.1
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chat-essential-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chat-essential-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * @since    0.0.1
	 */
	public function menu_main_page() {
		$slug = $_GET['page'];
  		$options = get_option('chat-essential');
		$settings_page = '';

		if (isset($options) && !empty($options)) {
			$options = array(
				'app_id' => $options['app_id'],
				'secret' => $options['secret'],
				'identity_verification' => '',
			);
		} else {
			$options = array(
				'app_id' => '',
				'secret' => '',
				'identity_verification' => '',
			);
		}

		switch ($slug) {
			case 'chat-essential':
			case 'chat-essential-settings':
				$settings_page = new Chat_Essential_Admin_Settings($options);
				break;
			case 'chat-essential-website':
				$settings_page = new Chat_Essential_Admin_Website($options);
				break;
			default:
				$settings_page = new Chat_Essential_Admin_Login($options);
		}

  		echo $settings_page->htmlUnclosed();
  		wp_nonce_field('chat-essential-update');
  		echo $settings_page->htmlClosed();
	}

}