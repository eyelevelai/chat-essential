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

		$title = localize('Settings');
		$nonce = $settings['nonce'];

		$h1 = localize('Live Chat');
		$h1_desc = localize('This is how you will live chat with people in chat');
		$l1 = localize('Integration Type');

		$l2 = localize('Phone Number');
		$l2_desc = localize('Enter the phone number that will be enabled for live chat. Only US phone numbers that are capable of SMS text are currently supported.');

		$h2 = localize('Lead Data');
		$h2_desc = localize('This is how you will export lead data from chat to your own systems');

		$l3 = localize('Email Address');
		$l3_desc = localize('Enter the email address (or addresses) that will receive lead data. If you use more than 1 email address, separate each address with a comma.');
		$l3_val = get_option('admin_email');

		$h3 = localize('Chat Interface Theme');
		$h3_desc = localize('These are the style settings for your chat window and bubble');
		$l4_val = "My Chat Theme";

		$h4 = localize('Business Hours');
		$h4_desc = localize('This defines the behavior of your chat during and after business hours');
		$l5_val = "My Business Hours";

		$h5 = localize('QR Code Style');
		$h5_desc = localize('These are the style settings for your QR codes');
		$l6_val = "My QR Code Style";

		$h6 = localize('Connected Facebook Pages');
		$h6_desc = localize('These are the Facebook Pages you have connected to the plugin');

		$l7_val = <<<END
			<td class="large-padding-bottom">
				<i>No pages added</i>
			</td>
			<td class="large-padding-bottom">
				<a href="https://ssp.eyelevel.ai/account">Edit</a>
			</td>
END;

		$fb_pages = array();
		$num_pages = count($fb_pages);
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
							<a href="https://ssp.eyelevel.ai/account">Edit</a>
						</td>
					</tr>
END;
			}
		}

		$submit = localize('Save Changes');

    	return <<<END
		<div class="wrap">
			<h1>$title</h1>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<form action="" method="post" name="settings_form">
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
											<select name="live-chat-type" id="live-chat-select">
												<option value="sms">SMS Text with People in Chat</option>
												<option value="slack" disabled>Slack (upgrade to premium)</option>
												<option value="msteams" disabled>Microsoft Teams (upgrade to premium)</option>
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
											<input type="tel" class="regular-text" value="" id="live-chat-phone-input" name="tel">
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
												<option value="zapier" disabled>Zapier (upgrade to premium)</option>
												<option value="mailchimp" disabled>Mailchimp (upgrade to premium)</option>
												<option value="hubspot" disabled>Hubspot (upgrade to premium)</option>
												<option value="marketo" disabled>Marketo (upgrade to premium)</option>
												<option value="salesforce" disabled>Salesforce (upgrade to premium)</option>
												<option value="custom" disabled>Custom Webhook (upgrade to premium)</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row">
											<label for="email">
												$l3
											</label>
										</th>
										<td>
											<input type="email" class="regular-text" value="$l3_val" id="track-event-email-input" name="email">
    										<p>
												$l3_desc
											</p>
										</td>
									</tr>
									<tr>
										<th colspan="2" class="no-padding-bottom">
											<h2>$h3</h2>
											<p>$h3_desc</p>
										</th>
									</tr>
									<tr>
										<td class="large-padding-bottom">
											$l4_val
										</td>
										<td class="large-padding-bottom">
											<a href="https://ssp.eyelevel.ai/account">Edit</a>
										</td>
									</tr>
									<tr>
										<th colspan="2" class="no-padding-bottom">
											<h2>$h4</h2>
											<p>$h4_desc</p>
										</th>
									</tr>
									<tr>
										<td class="large-padding-bottom">
											$l5_val
										</td>
										<td class="large-padding-bottom">
											<a href="https://ssp.eyelevel.ai/account">Edit</a>
										</td>
									</tr>
									<tr>
										<th colspan="2" class="no-padding-bottom">
											<h2>$h5</h2>
											<p>$h5_desc</p>
										</th>
									</tr>
									<tr>
										<td class="large-padding-bottom">
											$l6_val
										</td>
										<td class="large-padding-bottom">
											<a href="https://ssp.eyelevel.ai/account">Edit</a>
										</td>
									</tr>
									<tr>
										<th colspan="2" class="no-padding-bottom">
											<h2>$h6</h2>
											<p>$h6_desc</p>
										</th>
									</tr>
									$l7_val
									<tr>
										<th colspan="2">
											<p class="submit">
												<input type="submit" value="$submit" class="button button-primary ey-button" id="submit" name="submit_settings">
											</p>
										</th>
									</tr>
								</tbody>
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