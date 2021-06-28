<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Integrations {
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
	 * @param      array    $settings       The settings to load on the integrations management page.
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

		$title = localize('Integrations Settings');
		$nonce = wp_nonce_field('duplicatepage_action', 'duplicatepage_nonce_field');

		$hlc = localize('Live Chat');
		$hlc_desc = localize('This is how you will live chat with people in chat');
		$lit = localize('Integration Type');

		$llcp = localize('Phone Number');
		$hlcp_desc = localize('Enter the phone number that will be enabled for live chat. Only US phone numbers that are capable of SMS text are currently supported.');

		$hld = localize('Lead Data');
		$hld_desc = localize('This is how you will export lead data from chat to your own systems');

		$llde = localize('Email Address');
		$hlde_desc = localize('Enter the email address (or addresses) that will receive lead data. If you use more than 1 email address, separate each address with a comma.');
		$hlde_val = get_option('admin_email');

		$submit = localize('Save Changes');

    	return <<<END
		<div class="wrap">
			<h1>$title</h1>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<form action="" method="post" name="integrations_form">
							$nonce
							<table class="form-table">
								<tbody>
									<tr>
										<th colspan="2">
											<h2>$hlc</h2>
											<p>$hlc_desc</p>
										</th>
									</tr>
									<tr>
										<th scope="row">
											<label for="live-chat-type">
												$lit
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
												$llcp
											</label>
										</th>
										<td>
											<input type="tel" class="regular-text" value="" id="live-chat-phone-input" name="tel">
    										<p>
												$hlcp_desc
											</p>
										</td>
									</tr>
									<tr>
										<th colspan="2">
											<h2>$hld</h2>
											<p>$hld_desc</p>
										</th>
									</tr>
									<tr>
										<th scope="row">
											<label for="track-event-type">
												$lit
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
												$llde
											</label>
										</th>
										<td>
											<input type="email" class="regular-text" value="$hlde_val" id="track-event-email-input" name="email">
    										<p>
												$hlde_desc
											</p>
										</td>
									</tr>
								</tbody>
							</table>
							<p class="submit">
								<input type="submit" value="$submit" class="button button-primary" id="submit" name="submit_integrations">
							</p>
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