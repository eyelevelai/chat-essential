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

require_once 'vendor/autoload.php';

// Plugin version - https://semver.org
define( 'CHAT_ESSENTIAL_VERSION', '0.0.1' );

define( 'PLUGIN_SUBSCRIPTION', 'basic' );
define( 'WORDPRESS_PLUGIN_ID', '5ffb544f-e3f3-4108-95f8-0beb5139e22e' );
define( 'EYELEVEL_API_URL', 'https://devapi.eyelevel.ai' );
define( 'UPLOAD_BASE_URL', 'https://upload.eyelevel.ai/wordpress' );

define( 'MIN_TRAINING_CONTENT', 1000 );
define( 'MIN_TRAINING_PAGE_CONTENT', 100 );

$engines = array();
$engines[] = array(
	'name' => 'GPT-3',
	'engine' => 'gpt3',
);
define( 'CORE_ENGINES', $engines );

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

function localize($txt) {
	return __($txt, 'chat-essential');
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
