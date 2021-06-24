<?php

/**
 * Main menu landing page
 * 
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin/partials
 */
?>
<div class="wrap">
  <h1><?php echo esc_html( get_admin_page_title() ); ?> - OK!!</h1>
  <form action="options.php" method="post">
    <?php
      // output security fields for the registered setting "wporg_options"
      settings_fields( 'wporg_options' );
      // output setting sections and their fields
      // (sections are registered for "wporg", each field is registered to a specific section)
      do_settings_sections( 'wporg' );
      // output save settings button
      submit_button( __( 'Save Settings', 'textdomain' ) );
    ?>
  </form>
</div>