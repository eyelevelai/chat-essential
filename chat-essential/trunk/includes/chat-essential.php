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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-wp-escaper.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-identity-verification.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/chat-essential-api-client.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/chat-essential-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-error.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-login.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-ai.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-website.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-facebook-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-qr-code.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/chat-essential-admin-phone.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/chat-essential-public.php';

		$this->loader = new Chat_Essential_Loader();
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
	private function define_admin_hooks() {
		$plugin_admin = new Chat_Essential_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Chat_Essential_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
