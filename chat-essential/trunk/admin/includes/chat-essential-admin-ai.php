<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_AI {

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
	 * @access   private
	 * @var      array     $models    Pre-trained AI models.
	 */
	private $models = array(
		'GPT-3' => 0,
		'Home Renovations' => 1,
		'Residential Electrical' => 1,
		'Residential HVAC' => 1,
		'Residential Plumbing' => 1,
		'Tax Accounting' => 1,
		'Personal Injury Law' => 1,
		'Crossfit' => 1,
		'Pilates' => 1,
		'Yoga' => 1,
		'Mortgages' => 1,
		'Credit Cards' => 1
	);

	/**
	 * @since    0.0.1
	 * @param      array    $settings       The settings to load on the settings management page.
	 */
	public function __construct( $settings, $api ) {
		$this->settings = $settings;
		$this->api = $api;
	}

// 0 false
// 1 true
// 2 true
// 3 true
// 4 false
// 5 true

	public function html() {
    	$settings = $this->getSettings();

		$title = localize('Artificial Intelligence');
		$nonce = $settings['nonce'];

		$h1 = localize('Core Knowledge');
		$h1_desc = localize('Select the topics you want your AI to be knowledgeable about');

		$models = '';
		$n_models = count($this->models);
		$cnt = 0;
		foreach ($this->models as $idx => $val) {
			$col = '<td><input class="ai-checkbox" type="checkbox" name="topic-' . $idx . '" value="' . $idx . '"';
			if (!$val) {
				$col .= ' disabled checked';
			}
			$col .= ' /><div class="ai-input-label">' . $idx . '</div></td>';

			if ($cnt % 4) {
				$models .= $col;
			} else {
				if ($cnt > 0) {
					$models .= '</tr>';
				}
				$models .= '<tr>' . $col;
			}
			$cnt++;
			if ($cnt == $n_models) {
				$models .= '</tr>';
			}
		}

		$h2 = localize('Business Knowledge');
		$h2_desc = localize('Select the website content you want the AI to consume to learn about your business');

		$types = Site_Options::getTypes();
		$pages = '<tr><td colspan="4"><select name="site-type" id="siteTypeSelect">';
		foreach ($types as $idx => $val) {
			$pages .= '<option value="' . $idx . '">' . $val . '</option>';
		}
		$pages .= '</select></td></tr>';

		$h3 = localize('Voice Assistant');
		$h3_desc = localize('Turn this feature on to enable your AI to answer the phone');
		$switch = '<tr><td colspan="4"><label class="switch"><input type="checkbox" ';
		$switch .= 'checked';
		$h3_desc = localize('Your AI can be reached at the following phone number');
		$switch .= '><span class="slider"></span></label></td></tr>';

		$phone_number = '<tr><td colspan="4"><p>' . $h3_desc . '</p><input disabled type="tel" class="regular-text" value="(720) 356-3009" id="aiPhone" name="tel"></tr></td>';

		$submit = localize('Save Changes');

    	return <<<END
		<div class="wrap">
			<h1>$title</h1>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<a class="button button-primary ey-button top-margin" id="previewChat">Try It!</a>
						<form action="" method="post" name="ai_form" class="ey-form">
							$nonce
							<table class="form-table">
								<tbody>
									<tr>
										<th colspan="4" class="no-top">
											<h2>$h1</h2>
											<p>$h1_desc</p>
										</th>
									</tr>
									$models
									<tr>
										<td>
											<a href="?page=2">Load More</a>
										</td>
									</tr>
									<tr>
										<th colspan="4">
											<h2>$h2</h2>
											<p>$h2_desc</p>
										</th>
									</tr>
									$pages
									<tr>
										<th colspan="4">
											<h2>$h3</h2>
										</th>
									</tr>
									$switch
									$phone_number
									<tr>
										<th colspan="4">
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