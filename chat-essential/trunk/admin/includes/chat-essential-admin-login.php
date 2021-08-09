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

		$h1 = localize('<span class="ey-bold">SIGN IN</span> to your Chat Essential account.');
		$h2 = localize('');
		$footer = localize('Need an account? Click here to Sign Up.');
		$password_confirm = '';
		if (empty($settings[Chat_Essential_Admin::LOGGED_OUT_OPTION])) {
			$h1 = localize('<span class="ey-bold">SIGN UP</span> for a Chat Essential account.');
			$h2 = localize('No credit card required.');
			$footer = localize('Already have an account? Click here to Sign In.');

			$lc = localize('CONFIRM PASSWORD');
			$password_confirm = '<tr><td colspan="4" class="login-body med-font"><label for="password2">' . $lc . '</label><input autocomplete="password" type="password" class="login-input" id="password2" name="password2"></td><td class="login-spacer"></td></tr>';
		}

		$l1 = localize('EMAIL');
		$l1_val = get_option('admin_email');

		$l2 = localize('PASSWORD');

		$submit = localize('SUBMIT');

    	return <<<END
		<div class="wrap">
			<div class="metabox-holder columns-2">
				<div style="position: relative;">
					<form id="loginForm"  action="" method="post" name="login" class="web-rules-form login-form">
						$nonce
						<input type="hidden" name="request_type" value="login" />
						<table class="wp-list-table widefat fixed table-view-excerpt">
							<tr>
								<td colspan="5" scope="col" id="logo" class="manage-column column-logo">
									<img class="login-logo" src="$logo" />
								</td>
							</tr>
							<tr>
								<td colspan="4" class="login-body med-font">
									$h1
								</td>
								<td class="login-spacer"></td>
							</tr>
							<tr>
								<td colspan="4" class="login-body med-font">
									$h2
								</td>
								<td class="login-spacer"></td>
							</tr>
							<tr>
								<td colspan="4" class="login-body med-font">
									<label for="email">$l1</label>
									<input autocomplete="email" type="email" class="login-input" value="$l1_val" id="email" name="email">
								</td>
								<td class="login-spacer"></td>
							</tr>
							<tr>
								<td colspan="4" class="login-body med-font">
									<label for="password">$l2</label>
									<input autocomplete="password" type="password" class="login-input" id="password1" name="password">
								</td>
								<td class="login-spacer"></td>
							</tr>
							$password_confirm
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
									<a class="ey-link" id="footerBtn">$footer</a>
								</td>
							</tr>
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