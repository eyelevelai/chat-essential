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
        $install_date = get_option( 'chat_essential_activation_date' );
        $past_date = strtotime( '-3 days' );
        if ( $past_date >= $install_date ) {
            add_action('admin_notices', function () {
                $current_screen = get_current_screen();
                $user_id = get_current_user_id();
                if (!empty($_GET['chat-essential-notice-dismissed'])) {
                    add_user_meta( $user_id, 'chat_essential_notice_dismissed', 'true', true );
                }
                if (!get_user_meta( $user_id, 'chat_essential_notice_dismissed' ) && $current_screen->id === 'chat-essential_page_chat-essential-settings') {
                    echo '
                        <script>
                        function dismiss_notice() {
                            window.location = "?page=chat-essential-settings&chat-essential-notice-dismissed=1";
                        }
                        </script>
                        <div id="message" class="notice notice-info is-dismissible">
                            <p>Hey there! You\'ve been using the <strong>Chat Essential</strong> plugin for a while now. If you like the plugin, please support our team by leaving a <a target="_blank" href="https://wordpress.org/support/plugin/chat-essential/reviews/">review</a>!</p>
                            <button type="button" class="notice-dismiss" onclick="dismiss_notice();"></button>
                        </div>';
                }
            });
        }
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
/*
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - Facebook Page Chat', 'chat-essential'),
			__('Facebook Page', 'chat-essential'),
			'manage_options',
			'chat-essential-fb-page',
			array( $this, 'menu_main_page' ),
			20
		);
*/
		add_submenu_page(
			'chat-essential',
			__('Chat Essential - QR Codes Chat', 'chat-essential'),
			__('QR Codes', 'chat-essential'),
			'manage_options',
			'chat-essential-qr-code',
			array( $this, 'menu_main_page' ),
			20
		);

        if (CHAT_ESSENTIAL_SUBSCRIPTION == 'pro') {
            add_submenu_page(
                'chat-essential',
                __('Chat Essential - Add New Load On Rule', 'chat-essential'),
                __('New Load On Rule', 'chat-essential'),
                'manage_options',
                'chat-essential-add-new-rule',
                array( $this, 'menu_main_page' ),
                20
            );
        }
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
			$slug = sanitize_text_field($_GET['page']);
			$options = get_option(CHAT_ESSENTIAL_OPTION);
			switch ($slug) {
				case 'chat-essential':
				case 'chat-essential-ai':
					if (!empty($options) &&
						!empty($options['apiKey']) &&
						!empty($options['previewChat'])) {
						$chat = array(
							'origin' => 'web',
							'reset' => true,
							'clearcache' => true,
						);
						if (CHAT_ESSENTIAL_ENV !== 'prod') {
							$chat['env'] = CHAT_ESSENTIAL_ENV;
						}
						echo Chat_Essential_Pixel::generatePixel($options['apiKey'], $options['previewChat'], $chat);
					}
					break;
			}
		}
	}

	private function init_ai() {	
		$body = array(
			'siteType' => 'all',
		);

		$content = Site_Options::processOptions($body);
		if (count($content) < 1) {
			return;
		}
		$contentLen = 0;
		$submit = array();
		foreach ($content as $post) {
			$contentLen += strlen($post['content']);
		}
		if ($contentLen < CHAT_ESSENTIAL_MIN_TRAINING_CONTENT) {
			return;
		}

		$fname = uniqid(random_int(0, 10), true);
		$res = $this->api->upload($fname, $content);
		if ($res['code'] != 200) {
			return;
		}

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		$reqData = array(
			'fileUrl' => CHAT_ESSENTIAL_UPLOAD_BASE_URL . '/' . $fname . '.json',
			'metadata' => json_encode($body),
			'modelId' => $options['modelId'],
			'engines' => array(
				'gpt3',
			),
		);

		$res = $this->api->request($options['apiKey'], 'POST', 'nlp/train/' . $options['apiKey'], array(
			'nlp' => $reqData,
		), null);
		if ($res['code'] > 299) {
			return;
		}

		$options['initAI'] = true;
		update_option(CHAT_ESSENTIAL_OPTION, $options);
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
			foreach ($_POST['body']['engines'] as $engine) {
				$engines[] = sanitize_text_field($engine);
			}
			unset($_POST['body']['engines']);
		}
		if (!empty($_POST['body']['kits'])) {
			foreach ($_POST['body']['kits'] as $kid) {
				$kits[] = intval($kid);
			}
			unset($_POST['body']['kits']);
		}

		$content = Site_Options::processOptions($_POST['body']);
		if (count($content) < 1 &&
			!empty($_POST['body']['siteType']) &&
			$_POST['body']['siteType'] !== 'none') {
			wp_die('{"message":"No pages or posts fit the criteria you specified"}', 404);
		}
		$contentLen = 0;
		$submit = array();
		foreach ($content as $post) {
			$contentLen += strlen($post['content']);
		}
		if ($contentLen < CHAT_ESSENTIAL_MIN_TRAINING_CONTENT &&
			!empty($_POST['body']['siteType']) &&
			$_POST['body']['siteType'] !== 'none') {
			wp_die('{"message":"You have not included sufficient content to train your AI"}', 400);
		}

		$options = get_option(CHAT_ESSENTIAL_OPTION);
		$reqData = array(
			'metadata' => json_encode($_POST['body']),
			'modelId' => $options['modelId'],
		);
		if (!empty($_POST['body']['siteType']) &&
			$_POST['body']['siteType'] !== 'none') {
			$fname = uniqid(random_int(0, 10), true);
			$res = $this->api->upload($fname, $content);
			if ($res['code'] != 200) {
				wp_die($res['data'], $res['code']);
			}

			$reqData['fileUrl'] = CHAT_ESSENTIAL_UPLOAD_BASE_URL . '/' . $fname . '.json';
		}

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

		$jdata = json_decode($res['data'], true);
		wp_send_json(Chat_Essential_Utility::sanitize_json_array($jdata));

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
		$path = sanitize_text_field($_POST['path']);
		$path = str_replace('{apiKey}', $options['apiKey'], $path);

		$res = $this->api->request($options['apiKey'], $action, $path, $body, null);
		if ($res['code'] > 299) {
			wp_die($res['data'], $res['code']);
		}

		$jdata = json_decode($res['data'], true);
		wp_send_json(Chat_Essential_Utility::sanitize_json_array($jdata));

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
		$phone = sanitize_text_field($_POST['body']['phone']);

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
						$phone,
					),
				),
			),
		);
		if ($phone !== 'skip') {
			try {
				$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
				$pv = $phoneUtil->parse($phone, 'US');
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

		$jdata = json_decode($res['data'], true);
		wp_send_json(Chat_Essential_Utility::sanitize_json_array($jdata));

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

		if (!empty($_POST['body']['phones'])) {
			try {
				$phones = sanitize_text_field($_POST['body']['phones']);
				$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
				$pv = $phoneUtil->parse($phones, 'US');
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
		if (!empty($_POST['body']['phones'])) {
			$web_name = get_option('blogname');
			$phones = sanitize_text_field($_POST['body']['phones']);
			$data['integration'] = array(
				'sms' => array(
					'name' => $web_name . ' Phone',
					'phones' => array(
						$phones,
					),
				),
			);
		}

		$email = $options['email'];
		if (!empty($_POST['body']['email'])) {
			$email = sanitize_email($_POST['body']['email']);
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

		$jdata = json_decode($res['data'], true);
		wp_send_json(Chat_Essential_Utility::sanitize_json_array($jdata));

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

		$path = 'customer';
		$email = sanitize_email($_POST['body']['email']);
		$type = sanitize_text_field($_POST['body']['type']);
		$pass = sanitize_text_field($_POST['body']['password']);
		$data = null;
		if ($type == 'chat-essential-login') {
			$path = 'customer/' . $email;
		} else {
			$data = Chat_Essential_Utility::signup_data($email);
		}

		$res = $this->api->request($email, 'POST', $path, $data, array(
			'username' => $email,
			'password' => $pass,
		), ['timeout' => 120]);
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

		if ($type == 'chat-essential-login') {
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
			if (!empty($jdata['nlp']['model']['training']) &&
				!empty($jdata['nlp']['model']['training']['status']) &&
				$jdata['nlp']['model']['training']['status'] == 'complete') {
				$options['initAI'] = true;
			}
		}

		$options['apiKey'] = $jdata['apiKey'];
		$options['modelId'] = $jdata['nlp']['model']['modelId'];
		$options['email'] = $email;
		update_option(CHAT_ESSENTIAL_OPTION, $options);

		$jdata = json_decode($res['data'], true);
		wp_send_json(Chat_Essential_Utility::sanitize_json_array($jdata));

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

        $rid = sanitize_text_field($_POST['body']['rulesId']);
        $status = sanitize_text_field($_POST['body']['status']);
        Chat_Essential_Utility::update_web_status($rid, $status);

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
			'coreEngines' => CHAT_ESSENTIAL_CORE_ENGINES,
			'emailRegex' => Chat_Essential_Admin::EMAIL_REGEX,
			'passwordRegex' => Chat_Essential_Admin::PASSWORD_REGEX,
		);
		if (!empty($_GET['page'])) {
			$slug = sanitize_text_field($_GET['page']);
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
                case 'chat-essential-add-new-rule':
					$page_params['coreEngines'] = CHAT_ESSENTIAL_CORE_ENGINES;
					wp_register_script( 'showTypeOptions', plugin_dir_url( __FILE__ ) . 'js/show-site-options.js', array( 'jquery' ) );
					wp_enqueue_script( 'showTypeOptions' );
					wp_register_style( 'selectize-css', plugins_url( 'css/selectize.bootstrap3.css', __FILE__ ) );
					wp_enqueue_style( 'selectize-css' );
					wp_register_script( 'selectize-js', plugins_url( 'js/selectize.min.js', __FILE__ ), array( 'jquery' ) );
					wp_enqueue_script( 'selectize-js' );
					break;
                case 'chat-essential-website':
                    wp_register_script( 'jQuery-EC', 'https://code.jquery.com/jquery-3.6.0.min.js', null, null );
                    wp_enqueue_script('jQuery-EC');
                    wp_register_script( 'jQuery-UI-EC', 'https://code.jquery.com/ui/1.13.1/jquery-ui.min.js', null, null );
                    wp_enqueue_script('jQuery-UI-EC');
                    wp_register_style( 'jQuery-UI-CSS-EC', 'https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.min.css', null, null );
                    wp_enqueue_style('jQuery-UI-CSS-EC');
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
			$settings_page->html();
			return;
  		}

		$slug = sanitize_text_field($_GET['page']);
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

		if (!empty($options['apiKey']) &&
			!empty($options['modelId']) &&
			empty($options['initAI'])) {
			$this->init_ai();
		}

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
            case 'chat-essential-add-new-rule':
                $settings_page = new Chat_Essential_Admin_Add_New_Rule($options, $this->api);
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

  		$settings_page->html();
	}

}