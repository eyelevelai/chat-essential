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
	 * @param      array    $settings       The settings to load on the website management page.
	 */
	public function __construct( $settings, $api ) {
		$this->settings = $settings;
		$this->api = $api;
	}

	public function html() {
    	$settings = $this->getSettings();

		$title = localize('Website Chat');
		$nonce = $settings['nonce'];

		$hl = localize('Website Chat Rules #1');
		$hl_desc = localize('This defines the chat flow, theme, and business hours that load on your website');

		$h1 = localize('Status');
		$isOn = "ON";

		$h2 = localize('Chat Flow');
		$chat_val = $settings['website_name'] . ' Website Chat';
		$preview_url = '?action=preview';
		$preview_label = localize('Preview');
		$edit_url = '?action=edit';
		$edit_label = localize('Edit');
		$delete_url = '?action=delete';
		$delete_label = localize('Delete');

		$h3 = localize('Load On');
		$lot_val = 'Site Wide';

		$h4 = localize('Analytics');
		$v4 = localize('View');
		$v4_url = 'https://ssp.eyelevel.ai';

		$h5 = localize('Theme');
		$theme_val = $settings['website_name'] . ' Theme';

		$h6 = localize('Business Hours Settings');
		$offhours_val = $settings['website_name'] . ' Business Hours';

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
											$h1
										</th>
										<th colspan="2" scope="col" id="flow-name" class="manage-column column-flow-name">
											$h2
										</th>
										<th scope="col" id="load-on" class="manage-column column-load-on">
											$h3
										</th>
										<th scope="col" id="analytics" class="manage-column column-analytics">
											$h4
										</th>
										<th colspan="2" scope="col" id="theme" class="manage-column column-theme">
											$h5
										</th>
										<th colspan="2" scope="col" id="offhours" class="manage-column column-offhours">
											$h6
										</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td class="status column-status" data-colname="$h1">
											$isOn
										</td>
										<td colspan="2" class="flow-name column-flow-name" data-colname="$h2">
											<strong>$chat_val</strong>
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
										<td class="load-on column-load-on" data-colname="$h3">
											$lot_val
										</td>
										<td class="theme column-analytics" data-colname="$h4">
											<a href="$v4_url">$v4</a>
										</td>
										<td colspan="2" class="theme column-theme" data-colname="$h5">
											$theme_val
										</td>
										<td colspan="2" class="offhours column-offhours" data-colname="$h6">
											$offhours_val
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