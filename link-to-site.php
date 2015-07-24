<?php
/*
  * Plugin Name:       Link To Site
  * Plugin URI:        https://koombea.com/
  * Description:       The Link To Site plugin allows you to create a link style post commonly seen in blogs like DaringFireball.net, Marco.org, MacStories.net and SixColors.com. This means that you can link from your blog home or from RSS directly to an external page and not to your post's permalink.
  * Author:            Koombea
  * Contributors:      koombea
  * Version:           1.0.0
  * Author URI:        https://koombea.com/
  * License:           GPL-2.0+
  * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
  * Text Domain:       link-to-site
  * Domain Path:       /languages
*/

  if ( ! defined( 'WPINC' ) ) {
    die;
  }
  define( 'LTS_PREFIX',   '_linktosite_' );
  define( 'LTS_URL',      rtrim( plugin_dir_url( __FILE__ ), '/' ) );
  include 'includes/lts-class.php';
  include 'includes/external-permalink.php';
?>
