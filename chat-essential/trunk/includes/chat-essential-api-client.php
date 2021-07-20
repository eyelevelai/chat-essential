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

	private function request($type, $path) {
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
					return $response->getBody();
				}
			}
		} catch (GuzzleHttp\Exception\RequestException $e) {
			echo $e;
		}

		return "Internal plugin error";
	}

	/**
	 * @since    0.0.1
	 */
	public function getAccountInfo() {
		return $this->request('GET', 'customer');	
	}

}
