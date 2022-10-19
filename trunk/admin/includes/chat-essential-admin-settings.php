<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Settings {

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
	 * @param      array    $settings       The settings to load on the settings management page.
	 */
	public function __construct( $settings, $api ) {
		$this->settings = $settings;
		$this->api = $api;
	}

	public function html() {
    	$settings = $this->getSettings();

		$title = chat_essential_localize('Settings');
		$nonce = $settings['nonce'];

		$res = $this->api->request($settings['apiKey'], 'GET', 'customer/account/' . $settings['apiKey'], null, null);
		if ($res['code'] != 200) {
			$errMsg = new Chat_Essential_Admin_Error(
				Chat_Essential_API_client::error_content($res)
			);
			$errMsg->html();
			return;
		}

		if (empty($res['data'])) {
			wp_die('There was an issue loading your settings.', 500);
		}
		$data = json_decode($res['data']);

		$h1 = chat_essential_localize('Live Chat');
		$h1_desc = chat_essential_localize('This is how you will live chat with people in chat');
		$l1 = chat_essential_localize('Integration Type');

		$l2 = chat_essential_localize('Phone Number');
		$l2_desc = chat_essential_localize('Enter the phone number that will be enabled for live chat. Only US phone numbers that are capable of SMS text are currently supported.');
		$l2_val = '';
		if (!empty($data->integrations)) {
			foreach ($data->integrations as $int) {
				if (!empty($int->platform) &&
					$int->platform === 'mms') {
					if (!empty($int->phoneNumbers)) {
						$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
						$phones = explode(",", sanitize_text_field($int->phoneNumbers));
						foreach ($phones as $val) {
							if (strlen($l2_val)) {
								$l2_val .= ',';
							}
							$pv = $phoneUtil->parse($val, 'US');
							$ppv = $phoneUtil->format($pv, \libphonenumber\PhoneNumberFormat::RFC3966);
							$l2_val .= str_replace("tel:+1-", "", $ppv);
						}
						break;
					}
				}
			}
		}
		echo '<script>let og_phones = "' . esc_js($l2_val) . '";</script>';

		$h2 = chat_essential_localize('Lead Data');
		$h2_desc = chat_essential_localize('This is how you will export lead data from chat to your own systems');

		$l3 = chat_essential_localize('Email Address');
		$l3_desc = chat_essential_localize('Enter the email address that will receive lead data.');
		$l3_val = $settings['email'];
		echo '<script>let og_email = "' . esc_js($l3_val) . '";</script>';

		$h3 = chat_essential_localize('Chat Interface Themes');
		$h3_desc = chat_essential_localize('These are the style settings for your chat window and bubble');
		$themes = '';
		if (!empty($data->themes)) {
			foreach ($data->themes as $theme) {
				$editUrl = CHAT_ESSENTIAL_DASHBOARD_URL . '/account?themeId=' . sanitize_text_field($theme->themeId);
				if (defined('VENDASTA_ACCOUNT_ID') && !empty(VENDASTA_ACCOUNT_ID)) {
					$editUrl .= '&vendastaAccountId=' . VENDASTA_ACCOUNT_ID;
				}
				$themes .= '<tr><td class="large-padding-bottom">' . sanitize_text_field($theme->name) . '</td><td class="large-padding-bottom"><a href="' . $editUrl . '" target="_blank">Edit</a></td></tr>';
			}
		} else {
// create theme button
		}

		$h4 = chat_essential_localize('Business Hours Settings');
		$h4_desc = chat_essential_localize('This defines the behavior of your chat during and after business hours');
		$hours = '';
		if (!empty($data->offhoursSettings)) {
			$hours = '<tr><th colspan="2" class="no-padding-bottom"><h2>'.$h4.'</h2><p>'.$h4_desc.'</p></th></tr>';
			foreach ($data->offhoursSettings as $off) {
				$editUrl = CHAT_ESSENTIAL_DASHBOARD_URL . '/account?hoursId=' . sanitize_text_field($off->hoursId);
				if (defined('VENDASTA_APP_ID') && !empty(VENDASTA_APP_ID) && defined('VENDASTA_ACCOUNT_ID') && !empty(VENDASTA_ACCOUNT_ID)) {
					$editUrl .= '&vendastaAccountId=' . VENDASTA_ACCOUNT_ID;
				}
				$hours .= '<tr><td class="large-padding-bottom">' . sanitize_text_field($off->name) . '</td><td class="large-padding-bottom"><a href="' . $editUrl . '" target="_blank">Edit</a></td></tr>';
			}
		} else {
// here handle when no phone number entered
		}

		$h5 = chat_essential_localize('QR Code Styles');
		$h5_desc = chat_essential_localize('These are the style settings for your QR codes');
		$qrThemes = '';
		if (!empty($data->qrThemes)) {
			foreach ($data->qrThemes as $theme) {
				$editUrl = CHAT_ESSENTIAL_DASHBOARD_URL . '/account?qrThemeId=' . sanitize_text_field($theme->qrThemeId);
				if (defined('VENDASTA_APP_ID') && !empty(VENDASTA_APP_ID) && defined('VENDASTA_ACCOUNT_ID') && !empty(VENDASTA_ACCOUNT_ID)) {
					$editUrl .= '&vendastaAccountId=' . VENDASTA_ACCOUNT_ID;
				}
				$qrThemes .= '<tr><td class="large-padding-bottom">' . sanitize_text_field($theme->name) . '</td><td class="large-padding-bottom"><a href="' . $editUrl . '" target="_blank">Edit</a></td></tr>';
			}
		} else {
// create theme button
		}

/*
		$h6 = chat_essential_localize('Connected Facebook Pages');
		$h6_desc = chat_essential_localize('These are the Facebook Pages you have connected to the plugin');

		$l7_val = <<<END
			<td class="large-padding-bottom">
				<i>No pages added</i>
			</td>
			<td class="large-padding-bottom">
				<a href="https://ssp.eyelevel.ai/account" target="_blank">Edit</a>
			</td>
END;

									<tr>
										<th colspan="2" class="no-padding-bottom">
											<h2>$h6</h2>
											<p>$h6_desc</p>
										</th>
									</tr>
									$l7_val
*/

		$fb_pages = array();
		$num_pages = count($fb_pages);
        $chat_essential_edit_url = CHAT_ESSENTIAL_DASHBOARD_URL . '/account';
		if (defined('VENDASTA_APP_ID') && !empty(VENDASTA_APP_ID) && defined('VENDASTA_ACCOUNT_ID') && !empty(VENDASTA_ACCOUNT_ID)) {
			$chat_essential_edit_url .= '?vendastaAccountId=' . VENDASTA_ACCOUNT_ID;
		}
		if ($num_pages > 0) {
			$l7_val = "";
			foreach ($fb_pages as $idx => $val) {
				$l7_val = <<<END
					$l7_val
					<tr>
						<td class="large-padding-bottom">
							$val
						</td>
						<td class="large-padding-bottom">
							<a href="$chat_essential_edit_url" target="_blank">Edit</a>
						</td>
					</tr>
END;
			}
		}

/*
<option value="slack" disabled>Slack (upgrade to premium)</option>
<option value="msteams" disabled>Microsoft Teams (upgrade to premium)</option>

<option value="zapier" disabled>Zapier (upgrade to premium)</option>
<option value="mailchimp" disabled>Mailchimp (upgrade to premium)</option>
<option value="hubspot" disabled>Hubspot (upgrade to premium)</option>
<option value="marketo" disabled>Marketo (upgrade to premium)</option>
<option value="salesforce" disabled>Salesforce (upgrade to premium)</option>
<option value="custom" disabled>Custom Webhook (upgrade to premium)</option>
*/

		$submit = chat_essential_localize('Save Changes');
        $premium_banner = Chat_Essential_Utility::premium_banner();

    	echo <<<END
		<div class="wrap">
			<h1>$title</h1>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<a class="button button-primary ey-button top-margin" id="logoutBtn">Log Out</a>
						<form id="settingsForm" action="" method="post" name="settings_form">
							$nonce
							<table class="form-table">
								<tbody>
									<tr>
										<th colspan="2">
											<h2>$h1</h2>
											<p>$h1_desc</p>
										</th>
									</tr>
									<tr>
										<th scope="row">
											<label for="live-chat-type">
												$l1
											</label>
										</th>
										<td>
											<select name="live-chat-type" id="liveChatSelect">
												<option value="sms">SMS Text with People in Chat</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="tel">
												$l2
											</label>
										</th>
										<td>
											<input type="tel" class="regular-text" id="phone" name="tel" value="$l2_val">
    										<p>
												$l2_desc
											</p>
										</td>
									</tr>
									<tr>
										<th colspan="2">
											<h2>$h2</h2>
											<p>$h2_desc</p>
										</th>
									</tr>
									<tr>
										<th scope="row">
											<label for="track-event-type">
												$l1
											</label>
										</th>
										<td>
											<select name="track-event-type" id="track-event-select">
												<option value="email">Receive data by Email</option>
											</select>
										</td>
									</tr>
									<tr>
                                        <td colspan="2">$premium_banner</td>
                                    </tr>
									<tr>
										<th scope="row">
											<label for="email">
												$l3
											</label>
										</th>
										<td>
											<input type="email" class="regular-text" value="$l3_val" id="email" name="email">
    										<p>
												$l3_desc
											</p>
										</td>
									</tr>
									<tr>
										<td colspan="2" class="med-font status-msg ey-settings" id="statusMessage1"></td>
									</tr>
									<tr>
										<th colspan="2" class="ey-settings">
											<p class="submit ey-settings">
												<input type="submit" value="$submit" class="button button-primary ey-button" id="submit" name="submit_settings">
											</p>
										</th>
									</tr>
									<tr>
                                        <td colspan="2">$premium_banner</td>
                                    </tr>
									<tr>
										<th colspan="2" class="no-padding-bottom">
											<h2>$h3</h2>
											<p>$h3_desc</p>
										</th>
									</tr>
									$themes
									$hours
									<tr>
										<th colspan="2" class="no-padding-bottom">
											<h2>$h5</h2>
											<p>$h5_desc</p>
										</th>
									</tr>
									$qrThemes
								</tbody>
							</table>
						</form>
						<div id="confirmModal" style="display:none;">
    						<p id="confirmContent"></p>
							<div class="ey-modal-buttons">
								<input class="button button-primary ey-button" id="confirmChange" value="PROCEED">
								<input class="button button-primary ey-button-secondary ey-button-cancel" id="cancelChange" value="CANCEL">
							</div>
						</div>
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