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
	 * @since    0.0.1
	 * @access   private
	 * @var      string     $msg    The error message to display to the user.
	 */
	private $msg;

	/**
	 * @since    0.0.1
	 * @param    string    $msg     The raw, untranslated error message to display to the user.
	 */
	public function __construct( $msg ) {
		$this->msg = $msg;
	}

	public function html() {
		$err = localize($this->msg);

		$h1 = localize('Uh oh...We have a problem.');

    	return <<<END
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
							</tbody>
						</table>
					</form>
				</div>
			</div>
		</div>
END;
  	}

}