<?php

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
	 * @since    0.0.1
	 * @access   protected
	 * @var      Client    $client    Guzzle HTTP client.
	 */
	protected $client;

	public function __construct() {
		$this->client = new GuzzleHttp\Client([
			'base_uri' => EYELEVEL_API_URL,
		]);
	}

	public function upload($name, $data) {
		try {
			$request = new GuzzleHttp\Psr7\Request('GET', '/upload/wordpress?name=' . $name . '&type=json');
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
		} catch (GuzzleHttp\Exception\ClientException $e) {
			if ($e->hasResponse()) {
				$res = $e->getResponse();
				return array(
					'code' => $res->getStatusCode(),
					'data' => (string)($res->getBody()),
				);
			}
			return GuzzleHttp\Psr7\Message::toString($e->getRequest());
		}

		return array(
			'code' => 500,
			'data' => '{"message": "Internal plugin error"}',
		);
	}

	public function request($apiKey, $type, $path, $body, $auth) {
		try {
			$headers = [
				'X-API-Key' => WORDPRESS_PLUGIN_ID,
				'Content-Type' => 'application/json',
				'X-WordPress-Subscription' => PLUGIN_SUBSCRIPTION,
				'X-Customer-Key' => $apiKey,
			];
			if ($auth !== null) {
				$credentials = base64_encode($auth['username'] . ':' . $auth['password']);
				$headers['Authorization'] = 'Basic ' . $credentials;
			}
			if ($body !== null) {
				$body = json_encode($body);
			}
			$request = new GuzzleHttp\Psr7\Request($type, '/wordpress/' . $path, $headers, $body);
			$response = $this->client->send($request);
			if ($response) {
				$code = $response->getStatusCode();
				if ($code < 300 && $code > 199) {
					return array(
						'code' => $code,
						'data' => $response->getBody(),
					);
				}
			}
		} catch (GuzzleHttp\Exception\ClientException $e) {
			if ($e->hasResponse()) {
				$res = $e->getResponse();
				return array(
					'code' => $res->getStatusCode(),
					'data' => (string)($res->getBody()),
				);
			}
			return GuzzleHttp\Psr7\Message::toString($e->getRequest());
		}

		return array(
			'code' => 500,
			'data' => '{"message": "Internal plugin error"}',
		);
	}

}
