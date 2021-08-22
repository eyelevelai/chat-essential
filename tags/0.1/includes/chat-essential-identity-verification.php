<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */

class Chat_Essential_Identity_Verification {

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      array    $raw_data    The raw user information.
	 */
	private $raw_data;

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $secret_key    A key for hashing.
	 */
	private $secret_key;
  
	/**
	 * @since    0.0.1
	 * @param      array     $data             Raw user information.
	 * @param      string    $secret_key       The key to be used for hashing.
	 */
	public function __construct($data, $secret_key) {
	  $this->raw_data = $data;
	  $this->secret_key = $secret_key;
	}

	/**
	 * @since    0.0.1
	 */
	public function identityVerificationComponent() {
	  $secret_key = $this->getSecretKey();

	  if (empty($secret_key)) {
		return $this->emptyIdentityVerificationHashComponent();
	  }

	  if (array_key_exists("username", $this->getRawData())) {
		return $this->identityVerificationHashComponent("username");
	  }

	  if (array_key_exists("email", $this->getRawData())) {
		return $this->identityVerificationHashComponent("email");
	  }

	  return $this->emptyIdentityVerificationHashComponent();
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function emptyIdentityVerificationHashComponent() {
	  return array();
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 * @param    string     $key    The key to be used for hashing.
	 */
	private function identityVerificationHashComponent($key) {
	  $raw_data = $this->getRawData();

	  return array("user_hash" => hash_hmac("sha256", $raw_data[$key], $this->getSecretKey()));
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function getSecretKey() {
	  return $this->secret_key;
	}

	/**
	 * @since    0.0.1
	 * @access   private
	 */
	private function getRawData() {
	  return $this->raw_data;
	}

}
