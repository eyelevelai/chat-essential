<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Errors {

	protected $defaultHandler;

	/**
	 * @since    0.0.1
	 */
	public function init() {
		$this->defaultHandler = set_exception_handler(function(Throwable $ex) {
			$this->defaultHandler($ex);
		});
	}

}
