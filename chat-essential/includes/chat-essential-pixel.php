<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Pixel {
	/**
	 * @since    0.0.1
	 */
	public static function generatePixel($apiKey, $flowName, $options) {
		$opts = '';
		foreach ($options as $key => $val) {
			$opts .= ', ' . $key . ': ';
			$ty = gettype($val);
			if ($ty === 'string') {
				$opts .= "'" . $val . "'";
			} else if ($ty === 'boolean') {
				if ($val === true) {
					$opts .= 'true';
				} else {
					$opts .= 'false';
				}
			} else {
				$opts .= $val;
			}
		}
		return "<!-- EyeLevel Chat --> <script>!function(){if(window.eyelevel)return;window.eyelevel = [];window.eyusername = '" . $apiKey . "';window.eyflowname = '" . $flowName . "';window.eyelevel.push(['init', { username: window.eyusername, flowname: window.eyflowname" . $opts . " }]);var t=document.createElement('script');t.type='text/javascript',t.async=!0,t.src='https://cdn.eyelevel.ai/chat/eyelevel.js?v=1.3';var e=document.getElementsByTagName('script')[0];e.parentNode.insertBefore(t,e)}();</script> <!-- End EyeLevel Chat -->";
	}

}
