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

	public function request($type, $path) {
		try {
			$headers = [
				'X-API-Key' => WORDPRESS_PLUGIN_ID,
				'Content-Type' => 'application/json',
			];
			$request = new GuzzleHttp\Psr7\Request($type, $path, $headers);
			$response = $this->client->send($request);
			if ($response) {
				$code = $response->getStatusCode();
				if ($code == 200) {
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
