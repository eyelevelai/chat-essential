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
	const CHAT_ESSENTIAL_NONCE = 'chat-essential-update';

	/**
	 * @since    0.0.1
	 * @access   protected
	 * @var      Chat_Essential_API_Client    $api    Manages API calls to EyeLevel APIs.
	 */
	protected $api;

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
	
		$this->api = new Chat_Essential_API_Client();

		add_action( 'wp_ajax_chat_essential_get', array( $this, 'get_call' ) );
		add_action( 'wp_ajax_chat_essential_post', array( $this, 'post_call' ) );
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'network_admin_menu', 'add_menu');
		add_action( 'admin_footer', array( $this, 'add_footer' ) );
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
			__('Chat Essential - Artificial Intelligence', 'chat-essential'),
			__('AI', 'chat-essential'),
			'manage_options',
			'chat-essential',
			array( $this, 'menu_main_page' ),
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Settings', 'chat-essential'),
			__('Settings', 'chat-essential'),
			'manage_options',
			'chat-essential-settings',
			array( $this, 'menu_main_page' ),
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Website Chat', 'chat-essential'),
			__('Website', 'chat-essential'),
			'manage_options',
			'chat-essential-website',
			array( $this, 'menu_main_page' ),
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Facebook Page Chat', 'chat-essential'),
			__('Facebook Page', 'chat-essential'),
			'manage_options',
			'chat-essential-fb-page',
			array( $this, 'menu_main_page' ),
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - QR Codes Chat', 'chat-essential'),
			__('QR Codes', 'chat-essential'),
			'manage_options',
			'chat-essential-qr-code',
			array( $this, 'menu_main_page' ),
			20
		);
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Phone Chat', 'chat-essential'),
			__('Phone', 'chat-essential'),
			'manage_options',
			'chat-essential-phone',
			array( $this, 'menu_main_page' ),
			20
		);
	}

	/**
	 * @since    0.0.1
	 */
	public function add_footer() {
		$slug = $_GET['page'];
		switch ($slug) {
			case 'chat-essential':
			case 'chat-essential-ai':
				echo "<!-- EyeLevel Chat --> <script>!function(){if(window.eyelevel)return;window.eyelevel = [];window.eyusername = 'f2a864f6-987b-4dac-90f3-1029671c8e77';window.eyflowname = '01FB7YHH3TXCCZM4MHP4PT5FX4';window.eyelevel.push(['init', { username: window.eyusername, flowname: window.eyflowname, origin: 'web', reset: true, env: 'dev', clearcache: true }]);var t=document.createElement('script');t.type='text/javascript',t.async=!0,t.src='https://cdn.eyelevel.ai/chat/eyelevel.js?v=1.3';var e=document.getElementsByTagName('script')[0];e.parentNode.insertBefore(t,e)}();</script> <!-- End EyeLevel Chat -->";
				break;
		}
	}

	/**
	 * @since    0.0.1
	 */
	public function get_call() {
		if (wp_verify_nonce($_GET['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		if (empty($_GET['path'])) {
			wp_die('{"message":"path parameter is missing"}', 403);
		}

		$res = $this->api->request('GET', $_GET['path']);
		if ($res['code'] != 200) {
			wp_die($res['data'], $res['code']);
		}

		echo $res['data'];

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function post_call() {
		check_ajax_referer(Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE);

		wp_send_json($_POST);

		die();
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
		$options = get_option('chat-essential');
		if (!isset($options) || empty($options) || empty($options['apiKey'])) {
			$options = array(
				'apiKey' => 'f2a864f6-987b-4dac-90f3-1029671c8e77',
			);
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chat-essential-admin.js', array( 'jquery' ), $this->version, false );

		$page_params = array(
			'slug' => $_GET['page'],
		);

		wp_localize_script( $this->plugin_name, 'pageParams', $page_params );
	}

	/**
	 * @since    0.0.1
	 */
	public function menu_main_page() {
		if (!current_user_can('manage_options')) {
			$settings_page = new Chat_Essential_Admin_Error('You do not have sufficient permissions to access these settings.');
			echo $settings_page->html();
			return;
  		}

		$slug = $_GET['page'];
  		$options = get_option('chat-essential');
		$settings_page = '';

		$nonce = wp_nonce_field(Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE);
		$web_name = get_option('blogname');
		if (isset($options) && !empty($options)) {
			$options = array(
				'app_id' => $options['app_id'],
				'secret' => $options['secret'],
				'nonce' => $nonce,
				'website_name' => $web_name,
			);
		} else {
			$options = array(
				'app_id' => '',
				'secret' => '',
				'nonce' => $nonce,
				'website_name' => $web_name,
			);
//			$slug = 'login';
		}

		switch ($slug) {
			case 'chat-essential':
			case 'chat-essential-ai':
				$settings_page = new Chat_Essential_Admin_AI($options, $this->api);
				break;
			case 'chat-essential-settings':
				$settings_page = new Chat_Essential_Admin_Settings($options, $this->api);
				break;
			case 'chat-essential-website':
				$settings_page = new Chat_Essential_Admin_Website($options, $this->api);
				break;
			case 'chat-essential-fb-page':
				$settings_page = new Chat_Essential_Admin_FacebookPage($options, $this->api);
				break;
			case 'chat-essential-qr-code':
				$settings_page = new Chat_Essential_Admin_QRCode($options, $this->api);
				break;
			case 'chat-essential-phone':
				$settings_page = new Chat_Essential_Admin_Phone($options, $this->api);
				break;
			default:
				$settings_page = new Chat_Essential_Admin_Login($options, $this->api);
		}

  		echo $settings_page->html();
	}

}