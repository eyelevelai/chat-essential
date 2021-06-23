<?php

/**
 * @link              https://www.chatessential.com
 * @since             0.0.1
 * @package           Chat_Essential
 *
 * @wordpress-plugin
 * Plugin Name:       Chat Essential
 * Plugin URI:        http://wordpress.org/plugins/chat-essential/
 * Description:       Launch automated chats anywhere you advertise
 * Version:           0.0.1
 * Author:            Chat Essential
 * Author URI:        https://www.chatessential.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       chat-essential
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin version - https://semver.org
define( 'CHAT_ESSENTIAL_VERSION', '0.0.1' );

// Plugin activation code
function activate_chat_essential() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/chat-essential-activator.php';
	Chat_Essential_Activator::activate();
}

// Plugin deactivation code
function deactivate_chat_essential() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/chat-essential-deactivator.php';
	Chat_Essential_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_chat_essential' );
register_deactivation_hook( __FILE__, 'deactivate_chat_essential' );

require plugin_dir_path( __FILE__ ) . 'includes/chat-essential.php';

add_action( 'admin_menu', 'wporg_options_page' );
function wporg_options_page_html() {
    ?>
    <div class="wrap">
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <form action="options.php" method="post">
        <?php
        // output security fields for the registered setting "wporg_options"
        settings_fields( 'wporg_options' );
        // output setting sections and their fields
        // (sections are registered for "wporg", each field is registered to a specific section)
        do_settings_sections( 'wporg' );
        // output save settings button
        submit_button( __( 'Save Settings', 'textdomain' ) );
        ?>
      </form>
    </div>
    <?php
}
function wporg_options_page() {
    add_menu_page(
        'WPOrg',
        'WPOrg Options',
        'manage_options',
        'wporg',
        'wporg_options_page_html',
        plugin_dir_url(__FILE__) . 'images/qr-icon-gray.png',
        20
    );
}


/**
 * Init plugin
 *
 * @since    0.0.1
 */
function init_chat_essential() {

	$plugin = new Chat_Essential();
	$plugin->run();

}
init_chat_essential();
