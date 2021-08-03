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
	 * @var      array     $settings    The Chat Essential information for this WP site.
	 */
	private $settings;

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
		add_action( 'wp_ajax_chat_essential_site_options', array( $this, 'site_options' ) );
		add_action( 'wp_ajax_chat_essential_train', array( $this, 'train_ai' ) );
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
		if (!empty($_GET['page'])) {
			$slug = $_GET['page'];
			$options = get_option('chat-essential');
			switch ($slug) {
				case 'chat-essential':
				case 'chat-essential-ai':
					if (!empty($options) && !empty($options['apiKey'])) {
						echo "<!-- EyeLevel Chat --> <script>!function(){if(window.eyelevel)return;window.eyelevel = [];window.eyusername = '" . $options['apiKey'] . "';window.eyflowname = '01FB7YHH3TXCCZM4MHP4PT5FX4';window.eyelevel.push(['init', { username: window.eyusername, flowname: window.eyflowname, origin: 'web', reset: true, env: 'dev', clearcache: true }]);var t=document.createElement('script');t.type='text/javascript',t.async=!0,t.src='https://cdn.eyelevel.ai/chat/eyelevel.js?v=1.3';var e=document.getElementsByTagName('script')[0];e.parentNode.insertBefore(t,e)}();</script> <!-- End EyeLevel Chat -->";
					}
					break;
			}
		}
	}

	public function site_options() {
		global $wpdb;
	}

	/**
	 * @since    0.0.1
	 */
	public function train_ai() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		if (empty($_POST['body'])) {
			wp_die('{"message":"Missing request data"}', 400);
		}

		$content = Site_Options::processOptions($_POST['body']);
		if (count($content) < 1) {
			wp_die('{"message":"No pages or posts fit the criteria you specified"}', 404);
		}
		$contentLen = 0;
		$submit = array();
		foreach ($content as $post) {
			$contentLen += strlen($post['content']);
		}
		if ($contentLen < MIN_TRAINING_CONTENT) {
			wp_die('{"message":"You have not included sufficient content to train your AI"}', 400);
		}

		$fname = uniqid(random_int(0, 10), true);
		$res = $this->api->upload($fname, $content);
		if ($res['code'] != 200) {
			wp_die($res['data'], $res['code']);
		}

		$options = get_option('chat-essential');
		$res = $this->api->request('POST', 'nlp/train/' . $options['apiKey'], array(
			'nlp' => array(
				'fileUrl' => UPLOAD_BASE_URL . '/' . $fname . '.json',
				'metadata' => json_encode($_POST['body']),
				'modelId' => $options['modelId'],
			),
		));
		if ($res['code'] > 299) {
			wp_die($res['data'], $res['code']);
		}

		echo $res['data'];

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function get_call() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		if (empty($_POST['path'])) {
			wp_die('{"message":"Path parameter is missing"}', 400);
		}

		$options = get_option('chat-essential');
		$path = $_POST['path'];
		if (!empty($options) && !empty($options['apiKey'])) {
			$path = str_replace('{apiKey}', $options['apiKey'], $_POST['path']);
		}

		$res = $this->api->request('GET', $path, null);
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
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		if (empty($_POST['path'])) {
			wp_die('{"message":"Path parameter is missing"}', 403);
		}

		$options = get_option('chat-essential');
		$path = $_POST['path'];
		if (!empty($options) && !empty($options['apiKey'])) {
			$path = str_replace('{apiKey}', $options['apiKey'], $path);
		}

		$res = $this->api->request('POST', $path, null);
		if ($res['code'] != 200) {
			wp_die($res['data'], $res['code']);
		}

		echo $res['data'];

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
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chat-essential-admin.js', array( 'jquery' ), $this->version, false );

		$page_params = array();
		if (!empty($_GET['page'])) {
			$page_params['slug'] = $_GET['page'];
			switch ($page_params['slug']) {
				case 'chat-essential':
				case 'chat-essential-ai':
					wp_register_script( 'showTypeOptions', plugin_dir_url( __FILE__ ) . 'js/show-site-options.js', array( 'jquery' ) );
					wp_enqueue_script( 'showTypeOptions' );
					wp_register_style( 'selectize-css', plugins_url( 'css/selectize.bootstrap3.css', __FILE__ ) );
					wp_enqueue_style( 'selectize-css' );
					wp_register_script( 'selectize-js', plugins_url( 'js/selectize.min.js', __FILE__ ), array( 'jquery' ) );
					wp_enqueue_script( 'selectize-js' );
			}
		} else {
			$page_params['slug'] = '';
		}

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
		$web_name = get_option('blogname');
		$nonce = wp_nonce_field(Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE);
		if (isset($options) && !empty($options) && !empty($options['apiKey'])) {
			$options['nonce'] = $nonce;
			$options['slug'] = $slug;
			$options['website_name'] = $web_name;
		} else {
//			$slug = 'login';
			$options = array(
				'apiKey' => '',
				'nonce' => $nonce,
				'slug' => $slug,
				'website_name' => $web_name,
			);
		}

		$settings_page = '';
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