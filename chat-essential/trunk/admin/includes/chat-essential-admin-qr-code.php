<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_QRCode {

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
	 * @param      array    $settings       The settings to load on the QR code management page.
	 */
	public function __construct( $settings, $api ) {
		$this->settings = $settings;
		$this->api = $api;
	}

	public function html() {
    	$settings = $this->getSettings();

		$title = localize('QR Code Chat');
		$nonce = $settings['nonce'];

		$h1 = localize('Chat Flow');
		$v1 = $settings['website_name'] . ' QR Code Chat';
		$preview_url = '?action=preview';
		$preview_label = localize('Preview');
		$edit_url = '?action=edit';
		$edit_label = localize('Edit');
		$delete_url = '?action=delete';
		$delete_label = localize('Delete');

		$h2 = localize('QR Code');
		$l2 = localize('Download');
		$v2_url = 'https://ssp.eyelevel.ai';

		$h3 = localize('Analytics');
		$v3 = localize('View');
		$v3_url = 'https://ssp.eyelevel.ai';

		$h4 = localize('Theme');
		$v4 = $settings['website_name'] . ' Theme';

		$h5 = localize('QR Code Style');
		$v5 = $settings['website_name'] . ' QR Code Style';

		$h6 = localize('Business Hours Settings');
		$v6 = $settings['website_name'] . ' Business Hours';

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
										<th scope="col" id="qr-download" class="manage-column column-qr-download">
											$h2
										</th>
										<th scope="col" id="analytics" class="manage-column column-analytics">
											$h3
										</th>
										<th colspan="2" scope="col" id="theme" class="manage-column column-theme">
											$h4
										</th>
										<th colspan="2" scope="col" id="qr-style" class="manage-column column-qr-style">
											$h5
										</th>
										<th colspan="2" scope="col" id="offhours" class="manage-column column-offhours">
											$h6
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
										<td class="theme column-qr-download" data-colname="$h2">
											<a href="$v2_url">$l2</a>
										</td>
										<td class="theme column-analytics" data-colname="$h3">
											<a href="$v3_url">$v3</a>
										</td>
										<td colspan="2" class="theme column-theme" data-colname="$h4">
											$v4
										</td>
										<td colspan="2" class="theme column-qr-style" data-colname="$h5">
											$v5
										</td>
										<td colspan="2" class="offhours column-offhours" data-colname="$h6">
											$v6
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

	/**
	 * @since    0.0.1
	 */
	private function getSettings() {
    	return $this->settings;
  	}

}