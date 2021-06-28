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
	 * @param      array    $settings       The settings to load on the login management page.
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

    	return <<<END

    <link rel="stylesheet" property='stylesheet' href="https://marketing.intercomassets.com/assets/redesign-ead0ee66f7c89e2930e04ac1b7e423494c29e8e681382f41d0b6b8a98b4591e1.css">
    <style>
      #wpcontent {
        background-color: #ffffff;
      }
    </style>

    <div class="wrap">
      $dismissable_message

      <section id="main_content" style="padding-top: 70px;">
        <div class="container">
          <div class="cta">

            <div class="sp__2--lg sp__2--xlg"></div>
            <div id="oauth_content" style="$styles[app_id_link_style]">
              <div class="t__h1 c__red">Get started with Intercom</div>

              <div class="cta__desc">
                Chat with visitors to your website in real-time, capture them as leads, and convert them to customers. Install Intercom on your WordPress site in a couple of clicks.
              </div>

              <div id="get_intercom_btn_container" style="position:relative;margin-top:30px;">
                <a href="$auth_url">
                  <img src="https://static.intercomassets.com/assets/oauth/primary-7edb2ebce84c088063f4b86049747c3a.png" srcset="https://static.intercomassets.com/assets/oauth/primary-7edb2ebce84c088063f4b86049747c3a.png 1x, https://static.intercomassets.com/assets/oauth/primary@2x-0d69ca2141dfdfa0535634610be80994.png 2x, https://static.intercomassets.com/assets/oauth/primary@3x-788ed3c44d63a6aec3927285e920f542.png 3x"/>
                </a>
              </div>
            </div>

            <div class="t__h1 c__red" style="$styles[app_id_copy_title]">Intercom setup</div>
            <div class="t__h1 c__red" style="$styles[app_id_saved_title]">Intercom app ID saved</div>
            <div id="app_id_and_secret_content" style="$styles[app_id_row_style]">
              <div class="t__h1 c__red" style="$styles[app_id_copy_hidden]">Intercom has been installed</div>

              <div class="cta__desc">
                <div style="$styles[app_id_copy_hidden]">
                  Intercom is now set up and ready to go. You can now chat with your existing and potential new customers, send them targeted messages, and get feedback.
                  <br/>
                  <br/>
                  <a class="c__blue" href="https://app.intercom.com/a/apps/$app_id" target="_blank">Click here to access your Intercom Team Inbox.</a>
                  <br/>
                  <br/>
                  Need help? <a class="c__blue" href="https://docs.intercom.io/for-converting-visitors-to-users" target="_blank">Visit our documentation</a> for best practices, tips, and much more.
                  <br/>
                  <br/>
                </div>

                <div>
                  <div style="font-size:0.87em;$styles[app_id_copy_hidden]">
                  Learn more about our products : <a class="c__blue" href="https://www.intercom.com/customer-engagement" target="_blank">Messages</a>, <a class="c__blue" href="https://www.intercom.com/customer-support-software/knowledge-base" target="_blank">Articles</a> and <a class="c__blue" href="https://www.intercom.com/customer-support-software/help-desk" target="_blank">Inbox</a>.
                  </div>
                  <form method="post" action="" name="update_settings">
                    <table class="form-table" align="center" style="margin-top: 16px; width: inherit;">
                      <tbody>
                        <tr>
                          <th scope="row" style="text-align: center; vertical-align: middle;"><label for="intercom_app_id">App ID</label></th>
                          <td>
                            <input id="intercom_app_id" $styles[app_id_state] name="app_id" type="text" value="$app_id" class="$styles[app_id_class]">
                            <button type="submit" class="btn btn__primary cta__submit" style="$styles[button_submit_style]">Save</button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
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
                  </form>
                  <div style="$styles[app_id_copy_hidden]">
                    <div style="$styles[app_secret_link_style]">
                      <a class="c__blue" href="$auth_url_identity_verification">Authenticate with your Intercom application to enable Identity Verification</a>
                    </div>
                    <p style="font-size:0.86em">Identity Verification ensures that conversations between you and your users are kept private.<br/>
                    <br/>
                      <a class="c__blue" href="https://docs.intercom.com/configure-intercom-for-your-product-or-site/staying-secure/enable-identity-verification-on-your-web-product" target="_blank">Learn more about Identity Verification</a>
                    </p>
                    <br/>
                    <div style="font-size:0.8em">If the Intercom application associated with your Wordpress is incorrect, please <a class="c__blue" href="$auth_url">click here</a> to reconnect with Intercom, to choose a new application.</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
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