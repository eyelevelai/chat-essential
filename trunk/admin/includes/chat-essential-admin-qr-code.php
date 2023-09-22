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

	private function row($settings, $qr) {
		$qid = sanitize_text_field($qr->id);
		$flowName = sanitize_text_field($qr->name);

		$edit = chat_essential_localize('Edit');

		$analytics = chat_essential_localize('View');
		$analytics_url = CHAT_ESSENTIAL_DASHBOARD_URL . '/analytics/' . $qid;
		if (defined('VENDASTA_APP_ID') && !empty(VENDASTA_APP_ID) && defined('VENDASTA_ACCOUNT_ID') && !empty(VENDASTA_ACCOUNT_ID)) {
			$analytics_url .= '?reAuth=true&vAccountId=' . VENDASTA_ACCOUNT_ID . '&vPlanId=' . VENDASTA_APP_ID;
		}

		$theme_name = '';
		if (!empty($qr->theme) && !empty($qr->theme->name)) {
			$theme_name = sanitize_text_field($qr->theme->name);
		}
		$qr_theme_name = '';
		if (!empty($qr->qrTheme) && !empty($qr->qrTheme->name)) {
			$qr_theme_name = sanitize_text_field($qr->qrTheme->name);
		}
		$offhours_name = '';
		if (!empty($qr->offhoursSetting) && !empty($qr->offhoursSetting->name)) {
			$offhours_name = sanitize_text_field($qr->offhoursSetting->name);
		}

		$res = $this->api->request($settings['apiKey'], 'GET', 'publish/' . $settings['apiKey'] . '/' . $qid, null, null);
		if ($res['code'] != 200) {
			wp_die('There was an issue loading your settings.', $res['code']);
		}

		if (empty($res['data'])) {
			wp_die('There was an issue loading your settings.', 500);
		}
		$data = json_decode($res['data'], true);

		$download = '';
		$download_url = '';
		$preview = '';
		$preview_url = '';
		if (!empty($data)) {
			if (!empty($data['publish'])) {
				if (!empty($data['publish']['url'])) {
					$preview_url = esc_url($data['publish']['url']) . '&clearcache=true';
					$preview = chat_essential_localize('Preview');
				}
				if (!empty($data['publish']['qrLinks']) && !empty($data['publish']['qrLinks']['png@1000'])) {
					$download = chat_essential_localize('Download');
					$download_url = esc_url($data['publish']['qrLinks']['png@1000']);
				}
			}
		}

		return <<<END
		<tr>
		<td class="flow-name column-flow-name">
			<strong>$flowName</strong>
			<div class="row-actions visible">
				<span class="preview-web">
					<a href="$preview_url" target="_blank">$preview</a>
				</span>
			</div>
		</td>
		<td class="theme column-qr-download">
			<a href="$download_url" target="_blank">$download</a>
		</td>
		<td class="theme column-analytics">
			<a href="$analytics_url" target="_blank">$analytics</a>
		</td>
		<td class="theme column-theme">
			$theme_name
		</td>
		<td class="theme column-qr-style">
			$qr_theme_name
		</td>
		<td class="offhours column-offhours">
			$offhours_name
		</td>
	</tr>
END;
	}

	public function html() {
    	$settings = $this->getSettings();

		$title = chat_essential_localize('QR Code Chat');
		$nonce = $settings['nonce'];

		$res = $this->api->request($settings['apiKey'], 'GET', 'flow/' . $settings['apiKey'] . '?platform=qr&type=flow&data=full&modelId=' . $settings['modelId'], null, null);
		if ($res['code'] != 200) {
			$errMsg = new Chat_Essential_Admin_Error(
				Chat_Essential_API_client::error_content($res)
			);
			$errMsg->html();
			return;
		}

		if (empty($res['data'])) {
			wp_die('There was an issue loading your settings.', 500);
		}
		$data = json_decode($res['data']);
		$qrflows = '';
		if (!empty($data->flows)) {
			foreach ($data->flows as $flow) {
				$qrflows .= $this->row($settings, $flow);
			}
		} else {
			// empty state?
		}

		$h1 = chat_essential_localize('Chat Flow');
		$h2 = chat_essential_localize('QR Code');
		$h3 = chat_essential_localize('Analytics');
		$h4 = chat_essential_localize('Theme');
		$h5 = chat_essential_localize('QR Code Style');
		$h6 = chat_essential_localize('Business Hours Settings');

        $premium_banner = Chat_Essential_Utility::premium_banner();

    	echo <<<END
		<div class="wrap">
		<div class="upgrade-title-container">
			<h1 class="upgrade-title">$title</h1>
		</div>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<form action="" method="post" name="web_form" class="web-rules-form">
							$nonce
							<table class="wp-list-table widefat fixed striped table-view-excerpt">
								<thead class="manage-head">
									<tr>
										<th scope="col" id="flow-name" class="manage-column column-flow-name">
											$h1
										</th>
										<th scope="col" id="qr-download" class="manage-column column-qr-download">
											$h2
										</th>
										<th scope="col" id="analytics" class="manage-column column-analytics">
											$h3
										</th>
										<th scope="col" id="theme" class="manage-column column-theme">
											$h4
										</th>
										<th scope="col" id="qr-style" class="manage-column column-qr-style">
											$h5
										</th>
										<th scope="col" id="offhours" class="manage-column column-offhours">
											$h6
										</th>
									</tr>
								</thead>
								<tbody>
									$qrflows
								</tbody>
							</table>
						</form>
						$premium_banner
					</div>
				</div>
		</div>
END;
  	}

/*
<p class="submit">
<input disabled type="submit" value="$submit" class="button button-primary ey-button" id="submit" name="submit_web_rules">
<i>Upgrade to premium</i>
</p>
*/


	/**
	 * @since    0.0.1
	 */
	private function getSettings() {
    	return $this->settings;
  	}

}