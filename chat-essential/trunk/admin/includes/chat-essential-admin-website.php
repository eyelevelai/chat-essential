<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Website {
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
	 * @param      array    $settings       The settings to load on the website management page.
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

		$web_val = get_option('blogname');

		$title = localize('Website Settings');
		$nonce = wp_nonce_field('duplicatepage_action', 'duplicatepage_nonce_field');

		$hl = localize('Website Chat Rules #1');
		$hl_desc = localize('This defines the chat flow, theme, and business hours that load on your website');

		$status = localize('Status');
		$isOn = "ON";

		$lit = localize('Chat Flow');
		$chat_val = $web_val . ' Website Chat';
		$edit_url = '?action=edit';
		$delete_url = '?action=delete';

		$lot = localize('Load On');
		$lot_val = 'Site Wide';

		$llcp = localize('Theme');
		$theme_val = $web_val . ' Theme';

		$llde = localize('Business Hours Settings');
		$offhours_val = $web_val . ' Business Hours';

		$submit = localize('Add New Settings');

    	return <<<END
		<div class="wrap">
			<h1>$title</h1>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<form action="" method="post" name="web_form" class="web-rules-form">
							$nonce
							<table class="wp-list-table widefat fixed striped table-view-excerpt">
								<thead class="manage-head">
									<tr>
										<th scope="col" id="status" class="manage-column column-status">
											$status
										</th>
										<th colspan="2" scope="col" id="flow-name" class="manage-column column-flow-name">
											$lit
										</th>
										<th scope="col" id="load-on" class="manage-column column-load-on">
											$lot
										</th>
										<th colspan="2" scope="col" id="theme" class="manage-column column-theme">
											$llcp
										</th>
										<th colspan="2" scope="col" id="offhours" class="manage-column column-offhours">
											$llde
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="status column-status" data-colname="Status">
											$isOn
										</td>
										<td colspan="2" class="flow-name column-flow-name" data-colname="Flow name">
											<strong>$chat_val</strong>
											<div class="row-actions visible">
												<span class="edit">
													<a href="$edit_url">Edit</a>
												</span>
												<span class="delete">
													<a href="$delete_url">Delete</a>
												</span>
											</div>
										</td>
										<td class="load-on column-load-on" data-colname="Load On">
											$lot_val
										</td>
										<td colspan="2" class="theme column-theme" data-colname="Theme">
											$theme_val
										</td>
										<td colspan="2" class="offhours column-offhours" data-colname="Business Hours Settings">
											$offhours_val
										</td>
									</tr>
								</tbody>
							</table>
							<p class="submit">
								<input disabled type="submit" value="$submit" class="button button-primary" id="submit" name="submit_web_rules">
								<i>Upgrade to premium</i>
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