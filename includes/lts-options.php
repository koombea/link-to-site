<?php
  /**
   * Use Permalink Fuctions
   *
   * @link       https://koombea.com
   * @since      1.0.0
   *
   * @package    Link_To_Site
   * @subpackage Link_To_Site/includes
   */

  /**
   *
   * @since      1.0.0
   * @package    Link_To_Site
   * @subpackage Link_To_Site/includes
   * @author     Fabian Altahona <fabian.altahona@koombea.com>
   */
?>
<div class="wrap">
  <h2>
    Link To Site Options
    <i class="wp-menu-image dashicons-before dashicons-admin-plugins"></i>
  </h2>
  <form action="options.php" method="post">
    <div id="poststuff" class="metabox-holder">
        <?php settings_fields( '_linktosite_settings' ); ?>
        <?php do_settings_sections( '_linktosite_options' ); ?>
    </div>
    <p class="submit">
      <input type="submit" class="button-primary" value="Save Options" />
    </p>
  </form>
</div>
