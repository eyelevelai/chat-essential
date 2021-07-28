<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_FacebookPage {

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
	 * @param      array                        $settings  The settings to load on the Facebook page management page.
	 * @param      Chat_Essential_API_Client    $api       An EyeLevel API client.
	 */
	public function __construct( $settings, $api ) {
		$this->settings = $settings;
		$this->api = $api;
	}

	public function html() {
    	$settings = $this->getSettings();

		$title = localize('Facebook Page Chat');
		$nonce = $settings['nonce'];

		$h1 = localize('Chat Flow');
		$v1 = $settings['website_name'] . ' Facebook Page Chat';
		$preview_url = '?action=preview';
		$preview_label = localize('Preview');
		$edit_url = '?action=edit';
		$edit_label = localize('Edit');
		$delete_url = '?action=delete';
		$delete_label = localize('Delete');

		$h2 = localize('Facebook Page');
		$v2 = 'Test Page';

		$h3 = localize('Analytics');
		$v3 = localize('View');
		$v3_url = 'https://ssp.eyelevel.ai';

		$h4 = localize('Business Hours Settings');
		$v4 = $settings['website_name'] . ' Business Hours';

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
										<th colspan="2" scope="col" id="flow-name" class="manage-column column-flow-name">
											$h1
										</th>
										<th colspan="2" scope="col" id="facebook-page" class="manage-column column-facebook-page">
											$h2
										</th>
										<th scope="col" id="analytics" class="manage-column column-analytics">
											$h3
										</th>
										<th colspan="2" scope="col" id="offhours" class="manage-column column-offhours">
											$h4
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="2" class="flow-name column-flow-name" data-colname="$h1">
											<strong>$v1</strong>
											<div class="row-actions visible">
												<span class="preview-web">
													<a href="$preview_url">$preview_label</a>
												</span>
												<span class="edit">
													<a href="$edit_url">$edit_label</a>
												</span>
												<span class="delete">
													<a href="$delete_url">$delete_label</a>
												</span>
											</div>
										</td>
										<td colspan="2" class="theme column-facebook-page" data-colname="$h2">
											<strong>$v2</strong>
										</td>
										<td class="theme column-analytics" data-colname="$h3">
											<a href="$v3_url">$v3</a>
										</td>
										<td colspan="2" class="offhours column-offhours" data-colname="$h4">
											$v4
										</td>
									</tr>
								</tbody>
							</table>
							<p class="submit">
								<input disabled type="submit" value="$submit" class="button button-primary ey-button" id="submit" name="submit_web_rules">
								<i>Upgrade to premium</i>
							</p>
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