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
	const LOGGED_OUT_OPTION = 'logged-out';
	const PASSWORD_REGEX = '^(?=.*[a-z])(?=.*\W.*)[a-zA-Z0-9\S]{8,32}$';
	const EMAIL_REGEX = "^[-!#$%&'*+\/0-9=?A-Z^_a-z{|}~](\.?[-!#$%&'*+\/0-9=?A-Z^_a-z`{|}~])*@[a-zA-Z0-9](-*\.?[a-zA-Z0-9])*\.[a-zA-Z](-?[a-zA-Z0-9])+$";

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

		add_action( 'wp_ajax_chat_essential_switch_auth', array( $this, 'switch_auth' ) );
		add_action( 'wp_ajax_chat_essential_switch_platform_status', array( $this, 'switch_platform_status' ) );
		add_action( 'wp_ajax_chat_essential_auth', array( $this, 'auth' ) );
		add_action( 'wp_ajax_chat_essential_phone_signup', array( $this, 'phone_signup' ) );
		add_action( 'wp_ajax_chat_essential_settings_change', array( $this, 'settings_change' ) );
		add_action( 'wp_ajax_chat_essential_logout', array( $this, 'logout_call' ) );
		add_action( 'wp_ajax_chat_essential_get', array( $this, 'ajax_call' ) );
		add_action( 'wp_ajax_chat_essential_post', array( $this, 'ajax_call' ) );
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
/*
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Phone Chat', 'chat-essential'),
			__('Phone', 'chat-essential'),
			'manage_options',
			'chat-essential-phone',
			array( $this, 'menu_main_page' ),
			20
		);
*/
	}

	/**
	 * @since    0.0.1
	 */
	public function add_footer() {
		if (!empty($_GET['page'])) {
			$slug = $_GET['page'];
			$options = get_option(CHAT_ESSENTIAL_OPTION);
			switch ($slug) {
				case 'chat-essential':
				case 'chat-essential-ai':
					if (!empty($options) &&
						!empty($options['apiKey']) &&
						!empty($options['previewChat'])) {
						echo Chat_Essential_Pixel::generatePixel($options['apiKey'], $options['previewChat'], array(
							'origin' => 'web',
							'reset' => true,
							'env' => 'dev',
							'clearcache' => true,
						));
					}
					break;
			}
		}
	}

	/**
	 * @since    0.0.1
	 */
	public function train_ai() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		if (empty($_POST['body'])) {
			wp_die('{"message":"Corrupted plugin installation. Reinstall."}', 500);
		}

		$kits = array();
		$engines = array();
		if (!empty($_POST['body']['engines'])) {
			$engines = $_POST['body']['engines'];
			unset($_POST['body']['engines']);
		}
		if (!empty($_POST['body']['kits'])) {
			foreach ($_POST['body']['kits'] as $kid) {
				$kits[] = intval($kid);
			}
			unset($_POST['body']['kits']);
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

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		$reqData = array(
			'fileUrl' => UPLOAD_BASE_URL . '/' . $fname . '.json',
			'metadata' => json_encode($_POST['body']),
			'modelId' => $options['modelId'],
		);
		if (!empty($kits)) {
			$reqData['kits'] = $kits;
		}
		if (!empty($engines)) {
			$reqData['engines'] = $engines;
		}

		$res = $this->api->request($options['apiKey'], 'POST', 'nlp/train/' . $options['apiKey'], array(
			'nlp' => $reqData,
		), null);
		if ($res['code'] > 299) {
			wp_die($res['data'], $res['code']);
		}

		echo $res['data'];

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function ajax_call() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		if (empty($_POST['path'])) {
			wp_die('{"message":"Path parameter is missing"}', 400);
		}
		if (empty($_POST['action'])) {
			wp_die('{"message":"Action parameter is missing"}', 400);
		}
		$action = 'GET';
		$body = null;
		if ($_POST['action'] == 'chat_essential_post') {
			$action = 'POST';
		}

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		$path = $_POST['path'];
		$path = str_replace('{apiKey}', $options['apiKey'], $_POST['path']);

		$res = $this->api->request($options['apiKey'], $action, $path, $body, null);
		if ($res['code'] > 299) {
			wp_die($res['data'], $res['code']);
		}

		echo $res['data'];

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function phone_signup() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }
		if (empty($_POST['body']) ||
			empty($_POST['body']['phone'])) {
			wp_die('{"message":"Missing request parameters"}', 400);
		}
		$body = $_POST['body'];

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		if (empty($options) ||
			empty($options['apiKey'])) {
			wp_die('{"message":"Options are corrupted"}', 500);
		}

		$web_name = get_option('blogname');
		$path = 'customer/' . $options['apiKey'];
		$data = array(
			'integration' => array(
				'sms' => array(
					'name' => $web_name . ' Phone',
					'phones' => array(
						$body['phone'],
					),
				),
			),
		);
		if ($body['phone'] !== 'skip') {
			try {
				$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
				$pv = $phoneUtil->parse($body['phone'], 'US');
			} catch (\libphonenumber\NumberParseException $e) {
				wp_die('{"message":"Invalid phone number"}', 400);
			}
			$data['customer'] = array(
				'updateType' => 'phone-signup',
			);
		} else {
			$data['customer'] = array(
				'updateType' => 'phone-skip',
			);
		}

		$res = $this->api->request($options['apiKey'], 'POST', $path, $data, null);
		if ($res['code'] != 200) {
			wp_die($res['data'], $res['code']);
		}

		$jdata = json_decode($res['data'], true);
		if (empty($jdata) ||
			empty($jdata['apiKey']) ||
			empty($jdata['flows']) ||
			count($jdata['flows']) < 1) {
			wp_die('{"message":"Missing user account information"}', 500);
		}

		$webs = array();
		foreach ($jdata['flows'] as $flow) {
			if ($flow['platform'] === 'web') {
				$webs[] = $flow;
			}
		}

		if (empty($webs)) {
			wp_die('{"message":"Missing user account information"}', 500);
		}

		Chat_Essential_Utility::init_user($jdata['apiKey'], $webs);

		$options['signup-complete'] = true;
		update_option(CHAT_ESSENTIAL_OPTION, $options);

		echo $res['data'];

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function settings_change() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		if (empty($_POST['body']) ||
			(!isset($_POST['body']['phones']) && empty($_POST['body']['email']))) {
			wp_die('{"message":"Missing request parameters"}', 400);
		}
		$body = $_POST['body'];

		if (!empty($body['phones'])) {
			try {
				$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
				$pv = $phoneUtil->parse($body['phones'], 'US');
			} catch (\libphonenumber\NumberParseException $e) {
				wp_die('{"message":"Invalid phone number"}', 400);
			}
		}

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		if (empty($options) ||
			empty($options['apiKey'])) {
			wp_die('{"message":"Options are corrupted"}', 500);
		}

		$path = 'partner/settings/' . $options['apiKey'];

		$data = array();
		if (!empty($body['phones'])) {
			$data['integration'] = array(
				'sms' => array(
					'name' => $web_name . ' Phone',
					'phones' => array(
						$body['phones'],
					),
				),
			);
		}

		$email = $options['email'];
		if (!empty($body['email'])) {
			$email = $body['email'];
			$options['email'] = $email;
		}
		$data['customer'] = array(
			'email' => $email,
		);

		$res = $this->api->request($options['apiKey'], 'POST', $path, $data, null);
		if ($res['code'] != 200) {
			wp_die($res['data'], $res['code']);
		}

		$jdata = json_decode($res['data'], true);
		if (empty($jdata) ||
			empty($jdata['flows']) ||
			count($jdata['flows']) < 1) {
			wp_die('{"message":"Missing user account information"}', 500);
		}

		$webs = array();
		foreach ($jdata['flows'] as $flow) {
			if ($flow['platform'] === 'web') {
				$webs[] = $flow;
			}
		}

		if (empty($webs)) {
			wp_die('{"message":"Missing user account information"}', 500);
		}

		Chat_Essential_Utility::init_user($jdata['apiKey'], $webs);

		update_option(CHAT_ESSENTIAL_OPTION, $options);

		echo $res['data'];

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function auth() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }
		if (empty($_POST['body'])) {
			wp_die('{"message":"Body is missing"}', 400);
		}

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		if (empty($options)) {
			$options = array();
		}

		$body = $_POST['body'];
		$path = 'customer';
		$data = null;
		if ($body['type'] == 'chat-essential-login') {
			$path = 'customer/' . $body['email'];
		} else {
			$data = Chat_Essential_Utility::signup_data($body['email']);
		}

		$res = $this->api->request($body['email'], 'POST', $path, $data, array(
			'username' => $body['email'],
			'password' => $body['password'],
		));
		if ($res['code'] != 200) {
			wp_die($res['data'], $res['code']);
		}

		$jdata = json_decode($res['data'], true);
		if (empty($jdata) ||
			empty($jdata['apiKey']) ||
			empty($jdata['nlp']) ||
			empty($jdata['nlp']['model']) ||
			empty($jdata['nlp']['model']['modelId'])) {
			wp_die('{"message":"Missing user account information"}', 500);
		}

		if ($body['type'] == 'chat-essential-login') {
			if (!empty($jdata['flows']) &&
				count($jdata['flows']) > 0) {
				$webs = array();
				foreach ($jdata['flows'] as $flow) {
					if ($flow['platform'] === 'web') {
						$webs[] = $flow;
					}
				}

				if (empty($webs)) {
					wp_die('{"message":"Missing user account information"}', 500);
				}

				Chat_Essential_Utility::init_user($jdata['apiKey'], $webs);
				$options['signup-complete'] = true;
			}
		}

		$options['apiKey'] = $jdata['apiKey'];
		$options['modelId'] = $jdata['nlp']['model']['modelId'];
		$options['email'] = $body['email'];
		update_option(CHAT_ESSENTIAL_OPTION, $options);

		echo $res['data'];

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function logout_call() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		if (empty($options) ||
			empty($options['apiKey'])) {
				wp_die('Missing options information', 500);
		}

		Chat_Essential_Utility::logout($options['apiKey']);

		update_option(CHAT_ESSENTIAL_OPTION, array(
			Chat_Essential_Admin::LOGGED_OUT_OPTION => true,
		));

		echo 'OK';

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function switch_auth() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		if (!isset($options) || empty($options)) {
			$options = array(
				Chat_Essential_Admin::LOGGED_OUT_OPTION => true,
			);
		} else if (!empty($options[Chat_Essential_Admin::LOGGED_OUT_OPTION])) {
			unset($options[Chat_Essential_Admin::LOGGED_OUT_OPTION]);
		} else {
			$options[Chat_Essential_Admin::LOGGED_OUT_OPTION] = true;
		}

		update_option(CHAT_ESSENTIAL_OPTION, $options);

		echo 'OK';

		die();
	}

	/**
	 * @since    0.0.1
	 */
	public function switch_platform_status() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }
		if (empty($_POST['body'])) {
			wp_die('{"message":"Body is missing"}', 400);
		}

		$body = $_POST['body'];
		Chat_Essential_Utility::update_web_status($body['platformId'], $body['status']);

		echo 'OK';

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

		$slug = '';
		$page_params = array(
			'coreEngines' => CORE_ENGINES,
			'emailRegex' => Chat_Essential_Admin::EMAIL_REGEX,
			'passwordRegex' => Chat_Essential_Admin::PASSWORD_REGEX,
		);
		if (!empty($_GET['page'])) {
			$slug = $_GET['page'];
			if (!empty($_GET['logout']) &&
				$_GET['logout'] === 'true') {
				$slug = 'chat-essential-logout';
			} 
			if ($slug !== 'chat-essential-logout') {
				$options = get_option(CHAT_ESSENTIAL_OPTION);
				if (!isset($options) || empty($options)) {
					$slug = 'chat-essential-signup';
				} else {
					if (empty($options['apiKey'])) {
						$slug = 'chat-essential-signup';
						if (!empty($options[Chat_Essential_Admin::LOGGED_OUT_OPTION])) {
							$slug = 'chat-essential-login';
						}
					} else if (empty($options['signup-complete'])) {
						$slug = 'chat-essential-signup-phone';
					}
				}
			}

			switch ($slug) {
				case 'chat-essential':
				case 'chat-essential-ai':
					$page_params['coreEngines'] = CORE_ENGINES;
					wp_register_script( 'showTypeOptions', plugin_dir_url( __FILE__ ) . 'js/show-site-options.js', array( 'jquery' ) );
					wp_enqueue_script( 'showTypeOptions' );
					wp_register_style( 'selectize-css', plugins_url( 'css/selectize.bootstrap3.css', __FILE__ ) );
					wp_enqueue_style( 'selectize-css' );
					wp_register_script( 'selectize-js', plugins_url( 'js/selectize.min.js', __FILE__ ), array( 'jquery' ) );
					wp_enqueue_script( 'selectize-js' );
					break;
				case 'chat-essential-settings':
					add_thickbox();
				case 'chat-essential-signup-phone':
					wp_register_script( 'libphonenumber', plugins_url( 'js/libphonenumber-js.min.js', __FILE__ ), array( 'jquery' ) );
					wp_enqueue_script( 'libphonenumber' );
					break;
			}
		} else {
			$slug = '';
		}

		$page_params['slug'] = $slug;
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
		if (!empty($_GET['logout']) &&
			$_GET['logout'] === 'true') {
			$slug = 'chat-essential-logout';
		} 
		$options = get_option(CHAT_ESSENTIAL_OPTION);
		$web_name = get_option('blogname');
		$nonce = wp_nonce_field(Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE);

		if ($slug !== 'chat-essential-logout') {
			if (!isset($options) || empty($options)) {
				$options = array();
				$slug = 'chat-essential-signup';
			} else {
				if (empty($options['apiKey'])) {
					$slug = 'chat-essential-signup';
					if (!empty($options[Chat_Essential_Admin::LOGGED_OUT_OPTION])) {
						$slug = 'chat-essential-login';
					}
				} else if (empty($options['signup-complete'])) {
					$slug = 'chat-essential-signup-phone';
				}
			}
		}

		$options['nonce'] = $nonce;
		$options['slug'] = $slug;
		$options['website_name'] = $web_name;

		$settings_page = '';
		switch ($slug) {
			case 'chat-essential':
			case 'chat-essential-ai':
				if (empty($options['previewChat'])) {
					$res = $this->api->request($options['apiKey'], 'GET', 'flow/' . $options['apiKey'] . '?type=nlp', null, null);
					if ($res['code'] != 200) {
						$settings_page = new Chat_Essential_Admin_Error('There was an issue loading your AI settings.');
						break;
					}
					$data = json_decode($res['data']);
					if ($data->count !== 1 ||
						empty($data->flows) ||
						empty($data->flows[0]) ||
						empty($data->flows[0]->id)) {
						$settings_page = new Chat_Essential_Admin_Error('There was an issue loading your AI settings.');
						break;
					}
					$newOptions = get_option(CHAT_ESSENTIAL_OPTION);
					$newOptions['previewChat'] = $data->flows[0]->id;
					update_option(CHAT_ESSENTIAL_OPTION, $newOptions);
				}
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
			case 'chat-essential-logout':
				if (!empty($options) && !empty($options['apiKey'])) {
					Chat_Essential_Utility::logout($options['apiKey']);
				}

				update_option(CHAT_ESSENTIAL_OPTION, array(
					Chat_Essential_Admin::LOGGED_OUT_OPTION => true,
				));
				return;
			default:
				$settings_page = new Chat_Essential_Admin_Login($options, $this->api);
		}

  		echo $settings_page->html();
	}

}