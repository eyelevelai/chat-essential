<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Login {

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
		$nonce = $settings['nonce'];

		$logo = plugin_dir_url(__FILE__) . '../../images/logo.png';
		$submit = chat_essential_localize('SUBMIT');

		$l1 = chat_essential_localize('EMAIL');
		$l1_val = get_option('admin_email');
		$l2 = chat_essential_localize('PASSWORD');

		$h1 = '';
		$h2 = '';
		$form = '';
		$footer = '';
		$progress_bar = '';
		switch($settings['slug']) {
			case 'chat-essential-signup':
				$ht1a = chat_essential_localize('SIGN UP');
				$ht1b = chat_essential_localize(' for a Chat Essential account.');
				$h1 = <<<END
				<tr>
					<td colspan="4" class="login-body med-font">
						<span class="ey-bold">$ht1a</span> $ht1b
					</td>
					<td class="login-spacer"></td>
				</tr>
END;
				$ht2 = chat_essential_localize('No credit card required.');
				$h2 = <<<END
					<tr>
						<td colspan="4" class="login-body med-font">
							$ht2
						</td>
						<td class="login-spacer"></td>
					</tr>
END;
				$foottext = chat_essential_localize('Already have an account? Click here to Sign In.');
				$lc = chat_essential_localize('CONFIRM PASSWORD');
				$progress_bar = '<table class="wp-list-table widefat fixed table-view-excerpt progress-container"><tr><td id="step1" class="progress-step active-step"></td><td id="step2" class="progress-step"></td></tr></table>';
				$form = <<<END
					<tr>
						<td colspan="4" class="login-body med-font">
							<label for="email">$l1</label>
							<input autocomplete="email" type="email" class="login-input" value="$l1_val" id="eyEmail" name="email">
						</td>
						<td class="login-spacer"></td>
					</tr>
					<tr>
						<td colspan="4" class="login-body med-font">
							<label for="password">$l2</label>
							<input autocomplete="password" type="password" class="login-input" id="eyPassword1" name="password">
						</td>
						<td class="login-spacer"></td>
					</tr>
					<tr>
						<td colspan="4" class="login-body med-font">
							<label for="password2">$lc</label>
							<input autocomplete="password" type="password" class="login-input" id="eyPassword2" name="password2">
						</td>
						<td class="login-spacer"></td>
					</tr>
END;
				$footer = <<<END
					<tr>
						<td colspan="4" class="login-body">
							<a class="ey-link" id="footerBtn">$foottext</a>
						</td>
					</tr>
END;
				break;
			case 'chat-essential-login':
				$ht1a = chat_essential_localize('SIGN IN');
				$ht1b = chat_essential_localize(' to your Chat Essential account.');
				$h1 = <<<END
				<tr>
					<td colspan="4" class="login-body med-font">
						<span class="ey-bold">$ht1a</span> $ht1b
					</td>
					<td class="login-spacer"></td>
				</tr>
END;
				$foottext = chat_essential_localize('Need an account? Click here to Sign Up.');
				$form = <<<END
					<tr>
						<td colspan="4" class="login-body med-font">
							<label for="email">$l1</label>
							<input autocomplete="email" type="email" class="login-input" value="$l1_val" id="eyEmail" name="email">
						</td>
						<td class="login-spacer"></td>
					</tr>
					<tr>
						<td colspan="4" class="login-body med-font">
							<label for="password">$l2</label>
							<input autocomplete="password" type="password" class="login-input" id="eyPassword1" name="password">
						</td>
						<td class="login-spacer"></td>
					</tr>
END;
				$footer = <<<END
					<tr>
						<td colspan="4" class="login-body">
							<a class="ey-link" id="footerBtn">$foottext</a>
						</td>
					</tr>
END;
				break;
			case 'chat-essential-signup-phone':
				$h1 = <<<END
					<tr>
						<td colspan="5" class="phone-body med-font">
							<video class="phone-video" autoplay loop muted playsinline>
								<source src="https://cdn.eyelevel.ai/assets/wordpress/why-phone.webm" type="video/webm">
								<source src="https://cdn.eyelevel.ai/assets/wordpress/why-phone.mp4" type="video/mp4">
							</video>
						</td>
					</tr>
END;
				$ht1a = chat_essential_localize('ENTER A US MOBILE NUMBER');
				$ht1b = chat_essential_localize(' to enable SMS live chat. This is how you will chat with your website visitors.');
				$h3 = chat_essential_localize('(your number <span class="ey-bold">WILL NOT</span> be used for marketing purposes)');
				$l2 = chat_essential_localize('PHONE NUMBER');
				$foottext = chat_essential_localize('SKIP');
				$progress_bar = '<table class="wp-list-table widefat fixed table-view-excerpt progress-container"><tr><td id="step1" class="progress-step"></td><td id="step2" class="progress-step active-step"></td></tr></table>';
				$form = <<<END
					<tr>
						<td colspan="4" class="login-body med-font">
							<span class="ey-bold">$ht1a</span> $ht1b
						</td>
						<td class="login-spacer"></td>
					</tr>
					<tr>
						<td colspan="4" class="login-body med-font">
							<label for="phone">$l2</label>
							<input autocomplete="mobile tel" type="tel" class="login-input" id="eyPhone" name="phone">
						</td>
						<td class="login-spacer"></td>
					</tr>
					<tr>
						<td colspan="4" class="login-body med-font">
							$h3
						</td>
						<td class="login-spacer"></td>
					</tr>
END;
				$footer = <<<END
				<tr>
					<td colspan="4" class="login-body">
						<a class="ey-link" id="footerBtn">$foottext</a>
						<div class="small-font">(if you skip this step you will not be able to live chat with your website visitors)</div>
					</td>
				</tr>
END;
				break;
		}
		echo <<<END
		<div class="wrap">
			<div class="metabox-holder columns-2">
				<div style="position: relative;">
					<form id="eyLoginForm" action="" method="post" name="login" class="web-rules-form login-form">
						$nonce
						<input type="hidden" name="request_type" value="login" />
						$progress_bar
						<table class="wp-list-table widefat fixed table-view-excerpt">
							<tr>
								<td colspan="5" scope="col" id="logo" class="manage-column column-logo">
									<img class="login-logo" src="$logo" />
								</td>
							</tr>
							$h1
							$h2
							$form
							<tr>
								<td colspan="4" class="status-msg login-left" id="statusMessage1"></td>
							</tr>
							<tr>
								<td colspan="4" class="submit-button med-font">
									<button id="submit_login">$submit</button>
								</td>
							</tr>
							$footer
						</table>
					</form>
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