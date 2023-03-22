<?php

use EyeLevel\GuzzleHttp\Exception\ClientException;
use EyeLevel\GuzzleHttp\Exception\ServerException;
use EyeLevel\GuzzleHttp\Psr7\Message;
use EyeLevel\GuzzleHttp\Psr7\Request;
use EyeLevel\GuzzleHttp\Client;

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

	/**
	 * @since    0.0.1
	 * @access   protected
	 * @var      Client    $background    Guzzle HTTP client.
	 */
	protected $background;

	public function __construct() {
		$this->client = new Client([
			'base_uri' => CHAT_ESSENTIAL_API_URL,
		]);

		$this->alert = new Client([
			'base_uri' => CHAT_ESSENTIAL_ALERT_URL,
		]);

		$this->background = new Chat_Essential_Async($this);
	}

	public function queueUpload() {
		$this->background->data(array('name' => 'start'))->dispatch();
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
		} catch (Exception $e) {}
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

				$this->track(json_encode(
					array(
						'code' => $res->getStatusCode(),
						'data' => (string)($res->getBody()),
						'path' => '/upload/' . CHAT_ESSENTIAL_API_BASE . '?name=' . $name . '&type=json',
						'name' => get_option('blogname'),
						'url' => get_option('home'),
					)
				));

				return array(
					'code' => $res->getStatusCode(),
					'data' => (string)($res->getBody()),
				);
			}

			$this->track(json_encode(
				array(
					'message' => Message::toString($e->getRequest()),
					'path' => '/upload/' . CHAT_ESSENTIAL_API_BASE . '?name=' . $name . '&type=json',
					'name' => get_option('blogname'),
					'url' => get_option('home'),
				)
			));

			return Message::toString($e->getRequest());
		} catch (ServerException $e) {
			if ($e->hasResponse()) {
				$res = $e->getResponse();

				$this->track(json_encode(
					array(
						'code' => $res->getStatusCode(),
						'data' => (string)($res->getBody()),
						'name' => get_option('blogname'),
						'path' => '/upload/' . CHAT_ESSENTIAL_API_BASE . '?name=' . $name . '&type=json',
						'url' => get_option('home'),
					)
				));

				if ($res->getStatusCode() == 504) {
					return array(
						'code' => $res->getStatusCode(),
						'data' => 'The request to upload your content timed out. Please try again.',
					);
				}

				return array(
					'code' => $res->getStatusCode(),
					'data' => (string)($res->getBody()),
				);
			}
		} catch (Exception $e) {
			$this->track(json_encode(
				array(
					'message' => 'unknown upload exception',
					'name' => get_option('blogname'),
					'path' => '/upload/' . CHAT_ESSENTIAL_API_BASE . '?name=' . $name . '&type=json',
					'url' => get_option('home'),
				)
			));
		}

		return array(
			'code' => 500,
			'data' => '{"message": "Internal plugin error"}',
		);
	}

	public function request($apiKey, $type, $path, $body, $auth, $options = []) {
		try {
			$sub = CHAT_ESSENTIAL_SUBSCRIPTION;
			$opt = get_option(CHAT_ESSENTIAL_OPTION_SUBSCRIPTION);
			if (isset($opt) && !empty($opt)) {
				$sub = $opt;
			}
			$headers = [
				'X-API-Key' => CHAT_ESSENTIAL_PLUGIN_ID,
				'Content-Type' => 'application/json',
				'X-WordPress-Subscription' => $sub,
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
			if (!isset($options['timeout']) || empty($options['timeout'])) {
				$options['timeout'] = 60;
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
		} catch (ServerException $e) {
			if ($e->hasResponse()) {
				$res = $e->getResponse();

				$this->track(json_encode(
					array(
						'code' => $res->getStatusCode(),
						'data' => (string)($res->getBody()),
						'path' => $path,
						'type' => $type,
						'name' => get_option('blogname'),
						'url' => get_option('home'),
					)
				));

				if ($res->getStatusCode() == 504) {
					return array(
						'code' => $res->getStatusCode(),
						'data' => 'Your request timed out. Please try again.',
					);
				}

				return array(
					'code' => $res->getStatusCode(),
					'data' => (string)($res->getBody()),
				);
			}
		} catch (Exception $e) {
			$this->track(json_encode(
				array(
					'message' => 'unknown request exception',
					'path' => $path,
					'type' => $type,
					'name' => get_option('blogname'),
					'url' => get_option('home'),
				)
			));
		}

		return array(
			'code' => 500,
			'data' => '{"message": "Internal plugin error"}',
		);
	}

}

define( 'CHAT_ESSENTIAL_ASYNC', 'ce_async_request' );

class Chat_Essential_Async extends EyeLevel\WP_Async_Request {
	protected $action = CHAT_ESSENTIAL_ASYNC;

	/**
	 * @since    0.0.1
	 * @access   protected
	 * @var      Client    $client    Guzzle HTTP client.
	 */
	protected $client;

	public function __construct($client) {
		$this->client = $client;

		parent::__construct();
	}

	protected function handle() {
		$options = get_option(CHAT_ESSENTIAL_TRAIN_UPDATE);

		if (isset($options) && !empty($options)) {
			$fname = uniqid(random_int(0, 10), true);
			$content = Site_Options::processOptions($options['training']);

			$res = $this->client->upload($fname, $content);
			if ($res['code'] != 200) {
				return;
			}

			$reqData = array(
				'fileUrl' => CHAT_ESSENTIAL_UPLOAD_BASE_URL . '/' . CHAT_ESSENTIAL_API_BASE . '/' . $fname . '.json',
				'metadata' => json_encode($options['training']),
				'modelId' => $options['modelId'],
				'engines' => array(
					'gpt3',
				),
			);

			$res = $this->client->request($options['apiKey'], 'POST', 'nlp/train/' . $options['apiKey'], array(
				'nlp' => $reqData,
			), null);

			delete_option(CHAT_ESSENTIAL_TRAIN_UPDATE);
		}

		return false;
	}

}
