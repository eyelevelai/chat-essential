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

		$l1 = __('Duplicate Post Suffix', 'chat-essential');
		$h1 = __('Add a suffix for duplicate or clone post as Copy, Clone etc. It will show after title.', 'chat-essential');
		$v1 = !empty($opt['duplicate_post_suffix']) ? $opt['duplicate_post_suffix'] : '';
		$title = __('Integrations Settings', 'chat-essential');
		$nonce = wp_nonce_field('duplicatepage_action', 'duplicatepage_nonce_field');
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
										<th scope="row">
											<label for="duplicate_post_suffix">
												$l1
											</label>
										</th>
										<td>
											<input type="text" class="regular-text" value="$v1" id="duplicate_post_suffix" name="duplicate_post_suffix">
    										<p>
												$h1
											</p>
										</td>
									</tr>
								</tbody>
							</table>
							<p class="submit">
								<input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit_integrations">
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