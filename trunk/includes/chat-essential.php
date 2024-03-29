<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential {

	/**
	 * @since    0.0.1
	 * @access   protected
	 * @var      Chat_Essential_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * @since    0.0.1
	 */
	public function __construct() {
		if ( defined( 'CHAT_ESSENTIAL_VERSION' ) ) {
			$this->version = CHAT_ESSENTIAL_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'chat-essential';

		$this->load_dependencies();
		$this->set_locale();
//		$this->set_error_handler();
		$this->check_db();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-site-options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-pixel.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-wp-escaper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-identity-verification.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-api-client.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-utility.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-errors.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/chat-essential-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-error.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-login.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-ai.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-website.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-add-new-rule.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-facebook-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-qr-code.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-phone.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/chat-essential-public.php';

		if (defined('CHAT_ESSENTIAL_DEPENDENCIES')) {
			foreach (CHAT_ESSENTIAL_DEPENDENCIES as $k => $v) {
				require_once plugin_dir_path( dirname( __FILE__ ) ) . $v;
			}
		}

		$this->loader = new Chat_Essential_Loader();
	}

	private function check_db() {
		$plugin_util = new Chat_Essential_Utility();

		$this->loader->add_action( 'plugins_loaded', $plugin_util, 'db_check' );
	}

	private function check_hosted() {
		$plugin_util = new Chat_Essential_Utility();

		$this->loader->add_action( 'init', $plugin_util, 'hosted_check' );
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Chat_Essential_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_error_handler() {
		$plugin_errors = new Chat_Essential_Errors();

		$this->loader->add_action( 'wp_error_added', $plugin_errors, 'handler' );
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Chat_Essential_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'ey_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'ey_scripts' );
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Chat_Essential_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'ey_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'ey_scripts' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'ey_load_chat' );
	}

	/**
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * @since     0.0.1
	 * @return    Chat_Essential_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
