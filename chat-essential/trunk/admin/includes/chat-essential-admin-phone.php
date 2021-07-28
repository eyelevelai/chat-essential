<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Phone {

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

		$title = localize('Phone Chat');
		$nonce = $settings['nonce'];

		$h1_desc = localize('Turn this feature on to enable your AI to answer the phone');
		$switch = '<tr><td colspan="4"><label class="switch"><input type="checkbox" ';
		$switch .= 'checked';
		$h1_desc = localize('Your AI can be reached at the following phone number');
		$switch .= '><span class="slider"></span></label></td></tr>';

		$phone_number = '<tr><td colspan="4"><p>' . $h1_desc . '</p><input disabled type="tel" class="regular-text" value="(720) 356-3009" id="aiPhone" name="tel"></tr></td>';

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
									$switch
									$phone_number
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