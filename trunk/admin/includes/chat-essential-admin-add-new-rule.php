<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Add_New_Rule {

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

        if (!empty($_POST)) {
            echo '<pre>';
            print_r($_POST);
            echo '</pre>';
        }
    }

    public function html() {
        $settings = $this->getSettings();
        $title = chat_essential_localize('Add New Load On Rule');
        $nonce = $settings['nonce'];
        $siteOptions = Site_Options::typeSelector([]);
        $flowOptions = $this->getFlowOptions($settings);

        echo <<<END
		<div class="wrap">
			<div class="upgrade-title-container">
				<h1 class="upgrade-title">$title</h1>
			</div>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<form action="" method="post" name="web_form" class="web-rules-form ce-add-new-rule-table">
							$nonce
							<table class="wp-list-table widefat fixed striped table-view-excerpt">
								<tbody>
                                    <tr>
                                      <th><label for="flow">Flow Name</label></th>
                                      <td>
                                        <select name="data[flow]" id="flow">
                                          $flowOptions
                                        </select>
                                      </td>
                                    </tr>
                                    $siteOptions
                                    <tr>
                                      <th><label for="device_display">Device Display</label></th>
                                      <td>
                                        <select name="data[device_display]" id="device_display">
				                            <option value="both">Show on All Devices</option>
				                            <option value="desktop">Only Desktop</option>
				                            <option value="mobile">Only Mobile Devices</option>
                                          </select>
                                      </td>
                                    </tr>
                                    <tr>
                                      <th><label for="status">Status</label></th>
                                      <td>
                                        <select name="data[status]" id="status">
									        <option value="active">Active</option>
									        <option value="inactive">Inactive</option>
									      </select>
                                      </td>
                                    </tr>
								</tbody>
							</table>
							<button class="button button-primary ey-button top-margin">Save</button>
						</form>
					</div>
				</div>
		</div>
END;
    }

    private function getFlowOptions($settings) {
        $res = $this->api->request($settings['apiKey'], 'GET', 'flow/' . $settings['apiKey'] . '?platform=web&type=flow&data=full', null, null);

        if ($res['code'] != 200) {
            wp_die('There was an issue loading your settings.', $res['code']);
        }
        if (empty($res['data'])) {
            wp_die('There was an issue loading your settings.', 500);
        }
        $data = json_decode($res['data']);

        $webflows = '';
        if (!empty($data->flows)) {
            foreach ($data->flows as $flow) {
                $webflows .= "<option>$flow->name</option>";
            }
        }

        return $webflows;
    }

    /**
     * @since    0.0.1
     */
    private function getSettings() {
        return $this->settings;
    }

}