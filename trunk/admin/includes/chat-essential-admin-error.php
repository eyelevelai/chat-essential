<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin_Error {
	/**
	 * @since    0.2.0
	 * @access   private
	 * @var      string     $title      The error title to display to the user.
	 */
	private $title;

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      string     $message    The error message to display to the user.
	 */
	private $message;

	/**
	 * @since    0.2.0
	 * @access   private
	 * @var      boolean    $logout     Whether to add a logout button.
	 */
	private $logout;

	/**
	 * @since    0.0.1
	 * @param    string     $content    The content settings for this error message.
	 */
	public function __construct( $content ) {
		$title = 'Uh oh...We have a problem.';
		$message = 'There was an issue loading this page.';
		$logout = false;
		if (!empty($content)) {
			if (!empty($content['title'])) {
				$title = $content['title'];
			}
			if (!empty($content['message'])) {
				$message = $content['message'];
			}
			if (!empty($content['logout'])) {
				$logout = $content['logout'];
			}
		}
		$this->title = $title;
		$this->message = $message;
		$this->logout = $logout;
	}

	public function html() {
		$err = chat_essential_localize($this->message);

		$h1 = chat_essential_localize($this->title);

		$logout = '';
		if ($this->logout) {
			$logout = '<tr><td scope="col" class="manage-column column-logout"><a class="button button-primary ey-button logout-btn" id="logoutBtn">Log Out</a></td></tr>';
		}

    	echo <<<END
		<div class="wrap">
			<div class="metabox-holder columns-2">
				<div style="position: relative;">
					<form action="" method="post" name="login" class="web-rules-form error-form">
						<table class="wp-list-table widefat fixed table-view-excerpt">
							<thead class="manage-head">
								<tr>
									<th scope="col" class="manage-column column-status">
										$h1
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td scope="col" class="manage-column med-font">
										$err
									</td>
								</tr>
								$logout
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
END;
  	}

}