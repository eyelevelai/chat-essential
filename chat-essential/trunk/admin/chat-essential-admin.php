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
		add_action( 'wp_ajax_chat_essential_auth', array( $this, 'auth' ) );
		add_action( 'wp_ajax_chat_essential_logout', array( $this, 'logout_call' ) );
		add_action( 'wp_ajax_chat_essential_get', array( $this, 'ajax_call' ) );
		add_action( 'wp_ajax_chat_essential_post', array( $this, 'ajax_call' ) );
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

		$options = get_option('chat-essential');
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

		$options = get_option('chat-essential');
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
	 * @access   private
	 * @param    string               $color            Background color.
	 * @param    string               $dark             Darkest reference color.
	 * @param    string               $light            Lightest reference color.
	 * @return   string                                 The color that goes best with the given background.
	 */
	private function light_or_dark($color, $dark = '#000000', $light = '#FFFFFF') {
		return $this->is_light( $color ) ? $dark : $light;
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 * @param    string               $color            Hex color.
	 * @return   boolean                                Whether the color is light or dark.
	 */
	private function is_light($hex) {
		$c_r = hexdec( substr( $hex, 0, 2 ) );
		$c_g = hexdec( substr( $hex, 2, 2 ) );
		$c_b = hexdec( substr( $hex, 4, 2 ) );

		$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

		return array(
			'is_light' => $brightness > 155,
			'brightness' => $brightness,
		);
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 * @param    string               $color            Hex color.
	 * @return   string                                 Color with # removed and set to 6 characters.
	 */
	private function clean_color($color) {
		$hex = str_replace( '#', '', $color );
		$len = strlen($hex);
		if ($len != 3 && $len != 6 && $len != 9) {
			return;
		}
		if ($len == 3) {
			$hex = $hex . $hex;
		} else if ($len == 9) {
			$hex = substr($hex, 0, 6);
		}

		return $hex;
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 * @param    string               $email            The email address for signup.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	private function signup_data($email) {
		$web_name = get_option('blogname');
		$home_url = get_option('home');

		$options = array(
			'customer' => array(
				'email' => $email,
			),
			'integration' => array(
				'installations' => array()
			)
		);
		$options['integration']['installations'][] = array(
			'url' => $home_url,
			'name' => $web_name,
			'platform' => 'web'
		);

		$qrTheme = array();
		$theme = array();

		global $_wp_admin_css_colors;
		$opt = get_user_option( 'admin_color' );
		if ($opt) {
			$curr = $_wp_admin_css_colors[$opt];
			if (isset($curr)) {
				$primary = null;
				$secondary = null;
				$background = null;
				if (!empty($curr->colors)) {
					$c = $this->clean_color($curr->colors[0]);
					$cc = $this->is_light($c);
					$cc['color'] = '#'.$c;
					$primary = $cc;
				}
				if (!empty($curr->icon_colors)) {
					$choices = array();
					if (!empty($curr->icon_colors['base'])) {
						$c = $this->clean_color($curr->icon_colors['base']);
						$cc = $this->is_light($c);
						if ($cc['is_light'] != $primary['is_light']) {
							$cc['color'] = '#'.$c;
							$choices[] = $cc;
						}
					}
					if (!empty($curr->icon_colors['current'])) {
						$c = $this->clean_color($curr->icon_colors['current']);
						$cc = $this->is_light($c);
						if ($cc['is_light'] != $primary['is_light']) {
							$cc['color'] = '#'.$c;
							$choices[] = $cc;
						}
					}
					if (count($choices) == 2) {
						$light = $choices[0];
						$dark = $choices[1];
						if ($light['brightness'] < $dark['brightness']) {
							$light = $choices[1];
							$dark = $choices[0];
						}
						$secondary = $this->light_or_dark($primary['color'], $light['color'], $dark['color']);
					} else if (count($choices) == 1) {
						$secondary = $choices[0]['color'];
					}

				}
				if ($primary && $secondary) {
					$pc = $primary['color'];
					$theme['header'] = $pc;
					$theme['bubble'] = $pc;
					$theme['bubbleBackground'] = $secondary;
					if ($primary['is_light']) {
						$theme['button'] = $secondary;
						$theme['responseBackground'] = $secondary;
						$qrTheme['background'] = $pc;
						$qrTheme['foreground'] = $secondary;
					} else {
						$theme['button'] = $pc;
						$theme['responseBackground'] = $pc;
						$qrTheme['foreground'] = $pc;
						$qrTheme['background'] = $secondary;
					}
				}
			}
		}

		$icon = get_site_icon_url(200);
		$logo = get_theme_mod( 'custom_logo' );
		if ($icon || $logo) {
			if ($icon) {
				$theme['iconUrl'] = $icon;
				$qrTheme['imageUrl'] = $icon;
			}
			if ($logo) {
				$logo = wp_get_attachment_url($logo);
				if ($logo) {
					$logo = esc_url($logo);
					$theme['logoUrl'] = $logo;
				}
			}
		}

		if (!empty($theme)) {
			$theme['name'] = 'WordPress Plugin';
			$options['theme'] = $theme;
		}
		if (!empty($qrTheme)) {
			$qrTheme['name'] = 'WordPress Plugin';
			$options['qrTheme'] = $qrTheme;
		}

		$settings = get_option('chat-essential');
		if (!isset($settings) || empty($settings['dedicated_url'])) {
			if (!isset($settings)) {
				$settings = array();
			}
			$pid = wp_insert_post(array(
				'post_title' => 'Chat with Us | ' . $web_name,
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
				'post_type' => 'page'
			));

			if (!empty($pid)) {
				$url = get_page_link($pid);
				if ($url) {
					$options['integration']['installations'][] = array(
						'url' => $url,
						'name' => $web_name . ' - Chat Only',
						'platform' => 'web'
					);
					$settings['dedicated_url'] = $url;
					update_option('chat-essential', $settings);
				}
			}
		} else {
			$options['integration']['installations'][] = array(
				'url' => $settings['dedicated_url'],
				'name' => $web_name . ' - Chat Only',
				'platform' => 'web'
			);
		}

		return $options;
	}

	/**
	 * @since    0.0.1
	 */
	public function auth() {
		if (wp_verify_nonce($_POST['_wpnonce'], Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE) === false) {
            wp_die('', 403);
        }

		$body = $_POST['body'];
		$path = 'customer';
		$data = null;
		if ($body['type'] == 'login') {
			$path = 'customer/' . $body['email'];
		} else {
			$data = $this->signup_data($body['email']);
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
		update_option('chat-essential', array(
			'apiKey' => $jdata['apiKey'],
			'modelId' => $jdata['nlp']['model']['modelId'],		
		));

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

		update_option('chat-essential', array(
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

		$options = get_option('chat-essential');
		if (!isset($options) || empty($options)) {
			$options = array(
				Chat_Essential_Admin::LOGGED_OUT_OPTION => true,
			);
		} else if (!empty($options[Chat_Essential_Admin::LOGGED_OUT_OPTION])) {
			unset($options[Chat_Essential_Admin::LOGGED_OUT_OPTION]);
		} else {
			$options[Chat_Essential_Admin::LOGGED_OUT_OPTION] = true;
		}

		update_option('chat-essential', $options);

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
			}
		} else {
			$slug = '';
		}

		$options = get_option('chat-essential');
		if (!isset($options) || empty($options)) {
			$slug = 'signup';
		} else {
			if (empty($options['apiKey'])) {
				$slug = 'signup';
				if (!empty($options[Chat_Essential_Admin::LOGGED_OUT_OPTION])) {
					$slug = 'login';
				}
			}
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
		$options = get_option('chat-essential');
		$web_name = get_option('blogname');
		$nonce = wp_nonce_field(Chat_Essential_Admin::CHAT_ESSENTIAL_NONCE);
		if (!isset($options) || empty($options)) {
			$options = array();
			$slug = 'signup';
		} else {
			if (empty($options['apiKey'])) {
				$slug = 'signup';
				if (!empty($options[Chat_Essential_Admin::LOGGED_OUT_OPTION])) {
					$slug = 'login';
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