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
	 * @var      array     $settings    The Chat Essential information for this WP site.
	 */
	private $settings;

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      array     $styles
	 */
	private $styles;

	/**
	 * @since    0.0.1
	 * @param      array    $settings       The settings to load on the settings management page.
	 */
	public function __construct( $settings ) {
		if (isset($settings)) {
			$this->settings = $settings;
		} else {
			$this->settings = array(
				'app_id' => '',
				'secret' => '',
				'identity_verification' => '',
			);
		}
		$this->styles = $this->setStyles($settings);
	}

	/**
	 * @since    0.0.1
	 */
	public function getAuthUrl() {
		return 'https://www.eyelevel.ai/wp/auth?state='.get_site_url().'::'.wp_create_nonce('chat-essential-auth');
	}

	public function dismissibleMessage($text) {
    	return <<<END
  <div id="message" class="updated notice is-dismissible">
    <p>$text</p>
    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
  </div>
END;
  	}

	public function htmlUnclosed() {
    	$settings = $this->getSettings();
    	$styles = $this->getStyles();
    	$app_id = WP_Escaper::escAttr($settings['app_id']);
    	$secret = WP_Escaper::escAttr($settings['secret']);
    	$auth_url = $this->getAuthUrl();
    	$dismissable_message = '';
    	if (isset($_GET['appId'])) {
      		// Copying app_id from setup guide
      		$app_id = WP_Escaper::escAttr($_GET['appId']);
      		$dismissable_message = $this->dismissibleMessage('We\'ve copied your new Intercom app id below. click to save changes and then close this window to finish signing up for Intercom.');
    	}
    	if (isset($_GET['saved'])) {
      		$dismissable_message = $this->dismissibleMessage('Your app id has been successfully saved. You can now close this window to finish signing up for Intercom.');
    	}
    	if (isset($_GET['authenticated'])) {
      		$dismissable_message = $this->dismissibleMessage('You successfully authenticated with Intercom');
    	}

		$title = localize('Settings');
		$nonce = wp_nonce_field('duplicatepage_action', 'duplicatepage_nonce_field');

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
												<input type="submit" value="$submit" class="button button-primary" id="submit" name="submit_settings">
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

  	public function htmlClosed() {
    	$settings = $this->getSettings();
    	$styles = $this->getStyles();
    	$auth_url = $this->getAuthUrl();
    	$secret = WP_Escaper::escAttr($settings['secret']);
    	$app_id = WP_Escaper::escAttr($settings['app_id']);
    	$auth_url_identity_verification = '';
    	if (empty($secret) && !empty($app_id)) {
      		$auth_url_identity_verification = $auth_url.'&enable_identity_verification=1';
    	}

    	return <<<END
END;
  	}

	public function html() {
		return $this->htmlUnclosed() . $this->htmlClosed();
	}

	public function setStyles($settings) {
		$styles = array();
		$app_id = WP_Escaper::escAttr($settings['app_id']);
		$secret = WP_Escaper::escAttr($settings['secret']);
		$identity_verification = WP_Escaper::escAttr($settings['identity_verification']);

		// Use Case : Identity Verification enabled : checkbox checked and disabled
		if($identity_verification) {
		  	$styles['identity_verification_state'] = 'checked disabled';
		} else {
		  	$styles['identity_verification_state'] = '';
		}

		// Use Case : app_id here but Identity Verification disabled
		if (empty($secret) && !empty($app_id)) {
		  	$styles['app_secret_row_style'] = 'display: none;';
		  	$styles['app_secret_link_style'] = '';
		} else {
		  	$styles['app_secret_row_style'] = '';
		  	$styles['app_secret_link_style'] = 'display: none;';
		}

		// Copying appId from Intercom Setup Guide for validation
		if (isset($_GET['appId'])) {
			$app_id = WP_Escaper::escAttr($_GET['appId']);
			$styles['app_id_state'] = 'readonly';
			$styles['app_id_class'] = 'cta__email';
			$styles['button_submit_style'] = '';
			$styles['app_id_copy_hidden'] = 'display: none;';
			$styles['app_id_copy_title'] = '';
			$styles['identity_verification_state'] = 'disabled'; # Prevent from sending POST data about identity_verification when using app_id form
		} else {
		  	$styles['app_id_class'] = '';
		  	$styles['button_submit_style'] = 'display: none;';
		  	$styles['app_id_copy_title'] = 'display: none;';
		  	$styles['app_id_state'] = 'disabled'; # Prevent from sending POST data about app_id when using identity_verification form
		  	$styles['app_id_copy_hidden'] = '';
		}
	
		//Use Case App_id successfully copied
		if (isset($_GET['saved'])) {
		  	$styles['app_id_copy_hidden'] = 'display: none;';
		  	$styles['app_id_saved_title'] = '';
		} else {
		  	$styles['app_id_saved_title'] = 'display: none;';
		}
	
		// Display 'connect with intercom' button if no app_id provided (copied from setup guide or from Oauth)
		if (empty($app_id)) {
		  	$styles['app_id_row_style'] = 'display: none;';
		  	$styles['app_id_link_style'] = '';
		} else {
		  	$styles['app_id_row_style'] = '';
		  	$styles['app_id_link_style'] = 'display: none;';
		}

		return $styles;
	}

	/**
	 * @since    0.0.1
	 */
	private function getSettings() {
    	return $this->settings;
  	}

	/**
	 * @since    0.0.1
	 */
	private function getStyles() {
		return $this->styles;
	}

}