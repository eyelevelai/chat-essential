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
	);

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

		$title = localize('Artificial Intelligence');
		$nonce = $settings['nonce'];

		$h1 = localize('Core Knowledge');
		$loading = localize('Loading...');
		$h1_desc = localize('Select the topics you want your AI to be knowledgeable about');

		$h2 = localize('Business Knowledge');
		$h2_desc = localize('Select the website content you want your AI to consume to learn about your business');

		$training = array();
		if (!empty($settings['training'])) {
			$training = $settings['training'];
		}
		$siteOptions = Site_Options::typeSelector($training);

		$submit = localize('Train Your AI');

    	return <<<END
		<div class="wrap">
			<h1>$title</h1>
				<div class="med-font status-msg" id="statusMessage1"></div>
				<div id="pageContent" class="metabox-holder columns-2 ey-content">
					<div style="position: relative;">
						<a class="button button-primary ey-button top-margin" id="previewChat">Try It!</a>
						<form id="aiForm" action="" method="post" name="ai_form" class="ey-form">
							$nonce
							<table class="form-table">
								<tbody>
									<tr>
										<th colspan="2" class="no-top">
											<h2>$h1</h2>
											<p>$h1_desc</p>
										</th>
									</tr>
									<tr>
										<td colspan="2" class="ai-model-container">
											<table id="aiModels" class="form-table ai-model-table"></table>
										</td>
									</tr>
									<tr>
										<th colspan="2">
											<h2>$h2</h2>
											<p>$h2_desc</p>
										</th>
									</tr>
									$siteOptions
									<tr>
										<td colspan="2" class="med-font status-msg" id="statusMessage2"></td>
									</tr>
									<tr>
										<th colspan="2" class="status-th">
											<p class="submit status-p">
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