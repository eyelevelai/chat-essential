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
     * @access   private
     * @var      array     $flows    The list of Flows.
     */
    private $flows;

    /**
     * @since    0.0.1
     * @access   private
     * @var      array     $flows    The state of added rule
     */
    private $rule_added_state = array();

    /**
     * @since    0.0.1
     * @param      array    $settings       The settings to load on the website management page.
     */
    public function __construct( $settings, $api ) {
        $this->settings = $settings;
        $this->api = $api;
        $this->fetchFlows();
    }

    public function html() {
        $settings = $this->getSettings();
        $title = chat_essential_localize('Create Load On Rule');
        $nonce = $settings['nonce'];
        $flowOptions = $this->getFlowOptions();

        $delete = '';
        $rule = [];
        $rule_id = '';
        if (!empty($_GET['rid'])) {
            $delete = '<button id="deleteRule" value="' . $_GET['rid'] . '" class="button button-primary ey-button-secondary ey-button-delete top-margin">Delete</button>';
            $rule = Chat_Essential_Utility::get_rule($_GET['rid']);
            $rule = Site_Options::mapDBRecord($rule);
            $rule_id = '<input id="ruleId" type="hidden" name="rule_id" value="' . $_GET['rid'] . '" />';
        }

        $siteOptions = Site_Options::typeSelector($rule);

        if (!empty($rule)) {
            $title = chat_essential_localize('Edit Load On Rule');
        }

        echo <<<END
		<div class="wrap">
			<div class="add-rule-title-container">
				<h1 class="upgrade-title">$title</h1>
				<div class="med-font status-msg" id="statusMessage1"></div>
			</div>
				<div class="metabox-holder columns-2">
					<div style="position: relative;">
						<form id="ruleForm" action="" method="post" name="rule_form" class="web-rules-form ce-add-new-rule-table">
							$nonce
                            $rule_id
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
							<button class="button button-primary ey-button ey-button-save top-margin">Save</button>
                            $delete
                            <a class="button button-primary ey-button-secondary ey-button-cancel top-margin" href="?page=chat-essential-website">Done</a>
						</form>
                        <div id="deleteRuleModal" style="display:none;">
    						<p id="deleteRuleContent"></p>
							<div class="ey-modal-buttons buttons-centered">
								<input class="button button-primary ey-button" id="confirmDeleteRule" value="DELETE">
								<input class="button button-primary ey-button-secondary ey-button-cancel" id="cancelDeleteRule" value="CANCEL">
							</div>
						</div>
					</div>
				</div>
		</div>
END;
    }

    private function getRuleAddedNotice() {
        $n = 0;
        if (!empty($this->rule_added_state) && !empty($this->rule_added_state['n'])) {
            $n = $this->rule_added_state['n'];
        }
        switch ($n) {
            case 1:
                return '<div class="notice notice-success is-dismissible">The rule has been added</div>';
            case 2:
                return '<div class="notice notice-error is-dismissible">Something went wrong</div>';
            default:
                return '';
        }
    }

    private function fetchFlows() {
        $settings = $this->getSettings();
        $res = $this->api->request($settings['apiKey'], 'GET', 'flow/' . $settings['apiKey'] . '?platform=web&type=flow&data=full', null, null);

        if ($res['code'] != 200) {
            wp_die('There was an issue loading your settings.', $res['code']);
        }
        if (empty($res['data'])) {
            wp_die('There was an issue loading your settings.', 500);
        }
        $data = json_decode($res['data']);
        $this->flows = $data->flows ?: [];
    }

    private function getFlowById($flowId) {
        foreach ($this->flows as $flow) {
            if ($flow->id == $flowId) {
                return $flow;
            }
        }
        return false;
    }

    private function getFlowOptions() {
        $webflows = '';
        if (!empty($this->flows)) {
            foreach ($this->flows as $flow) {
                $fv = '{"id":"'.$flow->id.'","platformId":"'.$flow->platformId.'","apiKey":"'.$flow->apiKey.'"}';
                $webflows .= "<option value='".$fv."'>$flow->name</option>";
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