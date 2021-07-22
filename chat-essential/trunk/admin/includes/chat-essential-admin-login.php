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

		$h1 = localize('Sign into your Chat Essential account.');
		$h2 = localize('If you do not have an account, one will automatically be created for you.');

		$l1 = localize('EMAIL');
		$l1_val = get_option('admin_email');

		$l2 = localize('PASSWORD');

		$submit = localize('SUBMIT');

    	return <<<END
		<div class="wrap">
			<div class="metabox-holder columns-2">
				<div style="position: relative;">
					<form id="loginForm" name="login" class="web-rules-form login-form">
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
									<input type="email" class="login-input" value="$l1_val" id="email-login" name="email">
								</td>
								<td class="login-spacer"></td>
							</tr>
							<tr>
								<td colspan="4" class="login-body med-font">
									<label for="password">$l2</label>
									<input type="password" class="login-input" id="password-login" name="password">
								</td>
								<td class="login-spacer"></td>
							</tr>
							<tr>
								<td colspan="4" class="med-font error-msg" id="errorMessage">
								</td>
							</tr>
							<tr>
								<td colspan="4" class="submit-button med-font">
									<button id="submit_login">$submit</button>
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