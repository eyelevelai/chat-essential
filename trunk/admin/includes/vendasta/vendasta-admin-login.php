<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.2.0
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin/vendasta
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Vendasta_Admin_Login {

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      Chat_Essential_API_Client     $settings    Manages API calls to EyeLevel APIs.
	 */
	private $api;

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      array     $settings    The Chat Essential information for this WP site.
	 */
	private $settings;

	/**
	 * @since    0.0.1
	 * @param      array    $settings       The settings to load on the login management page.
	 */
	public function __construct( $settings, $api ) {
		$this->settings = $settings;
		$this->api = $api;
	}

	public function html() {
    	$settings = $this->getSettings();
		$slug = $settings['slug'];

		$logo = plugin_dir_url(__FILE__) . '../../../images/vendasta.png';
		$msg = '';

		$loginWindow = '';

		switch($slug) {
			case 'vendasta-error':
				$msg = $settings['error'];

				/*
				$btntxt = chat_essential_localize('Download New Plugin');
				$url = VENDASTA_STORE_BASE_URL.'/'.VENDASTA_APP_ID;
							<tr>
								<td colspan="5" class="med-font column-center vendasta-login">
									<a class="button button-primary ey-button vendasta-login-button" href="$url">$btntxt</a>
								</td>
							</tr>
				*/

				$loginWindow = <<<END
					<div class="web-rules-form login-form">
						<table class="wp-list-table widefat fixed table-view-excerpt">
							<tr>
								<td colspan="5" scope="col" id="logo" class="manage-column column-logo vendasta-logo">
									<img class="login-logo" src="$logo" />
								</td>
							</tr>
							<tr>
								<td colspan="5" class="status-msg-vendasta status-msg-vendasta-spaced error-msg" id="statusMessage1">
									$msg
								</td>
							</tr>
						</table>
					</div>
END;
				break;
			case 'chat-essential-signup':
			case 'chat-essential-login':
				if (!empty($settings['error'])) {
					$msg = '<td colspan="5" class="status-msg-vendasta error-msg" id="statusMessage1">'.$settings['error'].'</td>';
				} else if (!empty($settings['warning'])) {
					$msg = '<td colspan="5" class="status-msg-vendasta warn-msg" id="statusMessage1">'.$settings['warning'].'</td>';
				}

				$state = urlencode('{"username":"'.VENDASTA_ACCOUNT_ID.'","origin":"'.Chat_Essential_Utility::current_url().'","domain":"'.$settings['domain'].'"}');

				$url = VENDASTA_OAUTH_BASE.'?client_id='.VENDASTA_CLIENT_ID.'&prompt=login&scope=openid+profile&response_type=code&redirect_uri='.VENDASTA_OAUTH_REDIRECT.'&account_id='.VENDASTA_ACCOUNT_ID.'&state='.$state;

				$ht1a = chat_essential_localize('LOG IN');
				$ht1b = chat_essential_localize(' to your Vendasta account.');

				$login = chat_essential_localize('Log In');

				$loginWindow = <<<END
					<div class="web-rules-form login-form">
						<table class="wp-list-table widefat fixed table-view-excerpt">
							<tr>
								<td colspan="5" scope="col" id="logo" class="manage-column column-logo vendasta-logo">
									<img class="login-logo" src="$logo" />
								</td>
							</tr>
							<tr>
								<td colspan="5" class="med-font column-center">
									<a class="ey-bold" href="$url">$ht1a</a> $ht1b
								</td>
							</tr>
							<tr>
								$msg
							</tr>
							<tr>
								<td colspan="5" class="med-font column-center vendasta-login">
									<a class="button button-primary ey-button vendasta-login-button" href="$url">$login</a>
								</td>
							</tr>
						</table>
					</div>
END;
				break;
			case 'chat-essential-signup-phone':
				$nonce = $settings['nonce'];
				$ht1a = chat_essential_localize('ENTER A US MOBILE NUMBER');
				$ht1b = chat_essential_localize(' to enable SMS live chat. This is how you will chat with your website visitors.');
				$h3 = chat_essential_localize('(your number <span class="ey-bold">WILL NOT</span> be used for marketing purposes)');
				$l2 = chat_essential_localize('PHONE NUMBER');
				$foottext = chat_essential_localize('SKIP');
				$submit = chat_essential_localize('SUBMIT');

				$loginWindow = <<<END
					<form id="loginForm" action="" method="post" name="login" class="web-rules-form login-form">
						$nonce
						<input type="hidden" name="request_type" value="login" />
						<table class="wp-list-table widefat fixed table-view-excerpt progress-container">
							<tr>
								<td id="step1" class="progress-step"></td>
								<td id="step2" class="progress-step active-step"></td>
							</tr>
						</table>
						<table class="wp-list-table widefat fixed table-view-excerpt">
							<tr>
								<td colspan="5" scope="col" id="logo" class="manage-column column-logo">
									<img class="login-logo" src="$logo" />
								</td>
							</tr>
							<tr>
								<td colspan="5" class="phone-body med-font">
									<video class="phone-video" autoplay loop muted playsinline>
										<source src="https://cdn.eyelevel.ai/assets/wordpress/why-phone.webm" type="video/webm">
										<source src="https://cdn.eyelevel.ai/assets/wordpress/why-phone.mp4" type="video/mp4">
									</video>
								</td>
							</tr>
							<tr>
								<td colspan="4" class="login-body med-font">
									<span class="ey-bold">$ht1a</span> $ht1b
								</td>
								<td class="login-spacer"></td>
							</tr>
							<tr>
								<td colspan="4" class="login-body med-font">
									<label for="phone">$l2</label>
									<input autocomplete="mobile tel" type="tel" class="login-input" id="phone" name="phone">
								</td>
								<td class="login-spacer"></td>
							</tr>
							<tr>
								<td colspan="4" class="login-body med-font">
									$h3
								</td>
								<td class="login-spacer"></td>
							</tr>
							<tr>
								<td colspan="4" class="status-msg login-left" id="statusMessage1"></td>
							</tr>
							<tr>
								<td colspan="4" class="submit-button med-font">
									<button id="submit_login">$submit</button>
								</td>
							</tr>
							<tr>
								<td colspan="4" class="login-body">
									<a class="ey-link" id="footerBtn">$foottext</a>
									<div class="small-font">(if you skip this step you will not be able to live chat with your website visitors)</div>
								</td>
							</tr>
						</table>
					</form>
END;
				break;
		}

		echo <<<END
		<div class="wrap">
			<div class="metabox-holder columns-2">
				<div style="position: relative;">
					$loginWindow
				</div>
			</div>
		</div>
END;
  	}

	/**
	 * @since    0.0.1
	 */
	private function getSettings() {
    	return $this->settings;
  	}

}