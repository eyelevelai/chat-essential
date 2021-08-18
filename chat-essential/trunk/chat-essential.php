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

define( 'CHAT_ESSENTIAL_POST_TYPE', 'ce_hosted' );
define( 'CHAT_ESSENTIAL_OPTION', 'chat-essential' );

define( 'CHAT_ESSENTIAL_ENV', 'prod' );

define( 'PLUGIN_SUBSCRIPTION', 'basic' );
define( 'WORDPRESS_PLUGIN_ID', '5ffb544f-e3f3-4108-95f8-0beb5139e22e' );
define( 'EYELEVEL_API_URL', 'https://api.eyelevel.ai' );
define( 'DASHBOARD_URL', 'https://ssp.eyelevel.ai' );
define( 'UPLOAD_BASE_URL', 'https://upload.eyelevel.ai/wordpress' );
define( 'HOSTED_URL', 'https://chat.eyelevel.ai' );

define( 'MIN_TRAINING_CONTENT', 1000 );
define( 'MIN_TRAINING_PAGE_CONTENT', 100 );

global $chat_essential_db_version;
$chat_essential_db_version = '0.1';

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

$exHandler = set_exception_handler(function(Throwable $ex) {

	if (strpos($ex->getFile(), 'chat-essential') !== false) {
		$events = new GuzzleHttp\Client([
			'base_uri' => EYELEVEL_API_URL,
		]);
		$headers = [
			'X-API-Key' => WORDPRESS_PLUGIN_ID,
			'Content-Type' => 'application/json',
		];
		$body = json_encode(
			array(
				'line' => $ex->getLine(),
				'file' => $ex->getFile(),
				'message' => $ex->getMessage(),
				'code' => $ex->getCode(),
				'trace' => $ex->getTrace(),
				'name' => get_option('blogname'),
				'settings' => get_option(CHAT_ESSENTIAL_OPTION),
				'url' => get_option('home'),
			)
		);
		$request = new GuzzleHttp\Psr7\Request('POST', '/track', $headers, $body);
		$events->send($request);
	}

	if (isset($exHandler) && is_callable($exHandler)) {
		return call_user_func_array($exHandler, [$ex]);
	}

	throw $ex;
});

$errHandler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
	if (strpos($errfile, 'chat-essential') !== false) {
		$events = new GuzzleHttp\Client([
			'base_uri' => 'https://api.eyelevel.ai',
		]);
		$headers = [
			'X-API-Key' => WORDPRESS_PLUGIN_ID,
			'Content-Type' => 'application/json',
		];
		$body = json_encode(
			array(
				'line' => $errline,
				'file' => $errfile,
				'message' => $errstr,
				'code' => $errno,
				'name' => get_option('blogname'),
				'settings' => get_option(CHAT_ESSENTIAL_OPTION),
				'url' => get_option('home'),
			)
		);
		$request = new GuzzleHttp\Psr7\Request('POST', '/track', $headers, $body);
		$events->send($request);
	}

	if (isset($errHandler) && is_callable($errHandler)) {
		return call_user_func_array($errHandler, [$errno, $errstr, $errfile, $errline]);
	}
});

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
