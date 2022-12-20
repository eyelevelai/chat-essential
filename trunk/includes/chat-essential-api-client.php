<?php

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */

class Chat_Essential_API_client {
	/**
	 * @since    0.30
	 * @access   protected
	 * @var      Client    $alert    Guzzle HTTP client for alerts.
	 */
	protected $alert;

	/**
	 * @since    0.0.1
	 * @access   protected
	 * @var      Client    $client    Guzzle HTTP client.
	 */
	protected $client;

	public function __construct() {
		$this->client = new Client([
			'base_uri' => CHAT_ESSENTIAL_API_URL,
		]);

		$this->alert = new Client([
			'base_uri' => CHAT_ESSENTIAL_ALERT_URL,
		]);
	}

	public static function error_content( $res ) {
		$title = '';
		$msg = 'There was an issue loading your settings.';
		$logout = false;
		if (!empty($res['code'])) {
			switch ($res['code']) {
				case 401:
					$msg = 'Your account is not authorized to use this plugin. Please log out and log in with an authorized account.';
					$logout = true;
					$title = 'Not Authorized';
				default:
					if ( !empty($res['message']) ) {
						$msg = $res['message'];
					}
			}
		}

		return array(
			'logout' => $logout,
			'message' => $msg,
			'title' => $title,
		);
	}

	public function track($body) {
		try {
			$headers = [
				'X-API-Key' => CHAT_ESSENTIAL_PLUGIN_ID,
				'Content-Type' => 'application/json',
			];
			$request = new Request('POST', '/track', $headers, $body);
			$this->alert->send($request);
		} catch (ClientException $e) {}
	}

	public function upload($name, $data) {
		try {
			$request = new Request('GET', '/upload/' . CHAT_ESSENTIAL_API_BASE . '?name=' . $name . '&type=json');
			$response = $this->client->send($request);
			if ($response) {
				$code = $response->getStatusCode();
				if ($code == 200) {
					$body = $response->getBody();
					$jbody = json_decode($body, true);
					if (!empty($jbody['URL'])) {
						$response2 = $this->client->request('PUT', $jbody['URL'], [
							'json' => $data,
						]);
						$code2 = $response2->getStatusCode();
						if ($code == 200) {
							return array(
								'code' => 200,
								'data' => 'OK',
							);
						}
					}
				}
			}
		} catch (ClientException $e) {
			if ($e->hasResponse()) {
				$res = $e->getResponse();
				return array(
					'code' => $res->getStatusCode(),
					'data' => (string)($res->getBody()),
				);
			}
			return Message::toString($e->getRequest());
		}

		return array(
			'code' => 500,
			'data' => '{"message": "Internal plugin error"}',
		);
	}

	public function request($apiKey, $type, $path, $body, $auth, $options = []) {
		try {
			$headers = [
				'X-API-Key' => CHAT_ESSENTIAL_PLUGIN_ID,
				'Content-Type' => 'application/json',
				'X-WordPress-Subscription' => CHAT_ESSENTIAL_SUBSCRIPTION,
				'X-Customer-Key' => $apiKey,
				'X-Website-URL' => get_option('home'),
			];
			if ($auth !== null) {
				$credentials = base64_encode($auth['username'] . ':' . $auth['password']);
				$headers['Authorization'] = 'Basic ' . $credentials;
			}
			if ($body !== null) {
				$body = json_encode($body);
			}
			$request = new Request($type, '/' . CHAT_ESSENTIAL_API_BASE . '/' . $path, $headers, $body);
			$response = $this->client->send($request, $options);
			if ($response) {
				$code = $response->getStatusCode();
				if ($code < 300 && $code > 199) {
					return array(
						'code' => $code,
						'data' => $response->getBody(),
					);
				}
			}
		} catch (ClientException $e) {
			if ($e->hasResponse()) {
				$res = $e->getResponse();
				return array(
					'code' => $res->getStatusCode(),
					'data' => (string)($res->getBody()),
				);
			}
			return Message::toString($e->getRequest());
		}

		return array(
			'code' => 500,
			'data' => '{"message": "Internal plugin error"}',
		);
	}

}
