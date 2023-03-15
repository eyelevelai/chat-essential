<?php

/**
 * Plugin Name:       Chat Essential
 * @link              https://www.chatessential.com
 * @since             0.0.1
 * @package           Chat_Essential
 *
 * @wordpress-plugin
 * Plugin URI:        http://wordpress.org/plugins/chat-essential/
 * Description:       Launch automated chats anywhere you advertise
 * Version:           0.40
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
require('bin/libraries.phar');

require_once 'global.php';
require_once 'config.php';

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

function chat_essential_localize($txt) {
	return __($txt, 'chat-essential');
}

$exHandler = set_exception_handler(function(Throwable $ex) {

	if (strpos($ex->getFile(), 'chat-essential') !== false) {
		$events = new EyeLevel\GuzzleHttp\Client([
			'base_uri' => CHAT_ESSENTIAL_ALERT_URL,
		]);
		$headers = [
			'X-API-Key' => CHAT_ESSENTIAL_PLUGIN_ID,
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
				'url' => get_option('home'),
			)
		);
		$request = new EyeLevel\GuzzleHttp\Psr7\Request('POST', '/track', $headers, $body);
		$events->send($request);
	}

	if (isset($exHandler) && is_callable($exHandler)) {
		return call_user_func_array($exHandler, [$ex]);
	}

	throw $ex;
});

$errHandler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
	if (strpos($errfile, 'chat-essential') !== false) {
		$events = new EyeLevel\GuzzleHttp\Client([
			'base_uri' => CHAT_ESSENTIAL_ALERT_URL,
		]);
		$headers = [
			'X-API-Key' => CHAT_ESSENTIAL_PLUGIN_ID,
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
		$request = new EyeLevel\GuzzleHttp\Psr7\Request('POST', '/track', $headers, $body);
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
