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
  class linkToSite{
    function __construct(){
      add_action( 'admin_menu', array( $this, 'assets' ) );
      add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    function register_settings() {
      // flag our settings
      register_setting(
        LTS_PREFIX . 'settings',
        LTS_PREFIX . 'settings',
        array( $this, 'validate_settings' )
      );

      add_settings_section(
        LTS_PREFIX . 'options',
        '',
        array( $this, 'edit_options' ),
        LTS_PREFIX . 'options'
      );

      add_settings_field(
        LTS_PREFIX . 'category',
        'Default Category',
        array( $this, 'edit_category' ),
        LTS_PREFIX . 'options',
        LTS_PREFIX . 'options'
      );

      add_settings_field(
        LTS_PREFIX . 'post_format',
        'Post Format',
        array( $this, 'edit_post_format' ),
        LTS_PREFIX . 'options',
        LTS_PREFIX . 'options'
      );

      add_settings_field(
        LTS_PREFIX . 'future_publish',
        'Future Publish',
        array( $this, 'edit_future_publish' ),
        LTS_PREFIX . 'options',
        LTS_PREFIX . 'options'
      );

      // add_settings_field(
      //   LTS_PREFIX . 'external_url',
      //   'Use External Link',
      //   array( $this, 'use_external_permalink' ),
      //   LTS_PREFIX . 'options',
      //   LTS_PREFIX . 'options'
      // );

      add_settings_field(
        LTS_PREFIX . 'external_url_icon',
        'Show External Link Icon',
        array( $this, 'use_external_permalink_icon' ),
        LTS_PREFIX . 'options',
        LTS_PREFIX . 'options'
      );

      add_settings_field(
        LTS_PREFIX . 'prepopulate_slug',
        'Pre-populate Slug',
        array( $this, 'edit_prepopulate_slug' ),
        LTS_PREFIX . 'options',
        LTS_PREFIX . 'options'
      );

      add_settings_field(
        LTS_PREFIX . 'support_tags',
        'Support Tags',
        array( $this, 'edit_support_tags' ),
        LTS_PREFIX . 'options',
        LTS_PREFIX . 'options'
      );

      add_settings_field(
        LTS_PREFIX . 'bookmarklet',
        'Link To Site - Bookmarklet',
        array( $this, 'edit_bookmarklet' ),
        LTS_PREFIX . 'options',
        LTS_PREFIX . 'options'
      );
    }

    function validate_settings( $input ) {
      return $input;
    }

    function edit_options(){}

    function edit_category(){
      $settings   = get_option( LTS_PREFIX . 'settings' );
      $categories = get_categories( 'hide_empty=0' );
      ?>
      <select style="width: 30%;" name="<?php echo LTS_PREFIX; ?>settings[category]">
        <option value="0">- No Category -</option>
        <?php foreach( $categories as $category ) : ?>
          <option value="<?php echo absint( $category->cat_ID ); ?>"<?php if( isset( $settings['category'] ) && $settings['category'] == $category->cat_ID ) : ?> selected="selected"<?php endif; ?>><?php echo esc_html( $category->cat_name ); ?></option>
        <?php endforeach; ?>
      </select>
      <?php
    }

    function edit_post_format() {
      if ( ! current_theme_supports( 'post-formats' ) ) {
        echo 'Your active theme does not support Post Formats.';
        return;
      }

      $settings       = get_option( LTS_PREFIX . 'settings' );
      $post_formats   = get_theme_support( 'post-formats' );
      ?>
      <select style="width: 30%;" name="<?php echo LTS_PREFIX; ?>settings[post_format]">
        <?php if ( is_array( $post_formats[0] ) ) : ?>
          <option value="0">Standard</option>
          <?php foreach( $post_formats[0] as $post_format ) : ?>
              <option value="<?php echo esc_attr( $post_format ); ?>"<?php if( isset( $settings['post_format'] ) && $settings['post_format'] == $post_format ) : ?> selected="selected"<?php endif; ?>><?php echo esc_html( ucfirst( $post_format ) ); ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
      <?php
    }

    function edit_future_publish()
    {
      $settings       = get_option( LTS_PREFIX . 'settings' );
      $timeframe_min  = !isset( $settings['future_publish']['min'] ) || $settings['future_publish']['min'] === '' ? '' : intval( $settings['future_publish']['min'] );
      $timeframe_max  = !isset( $settings['future_publish']['max'] ) || $settings['future_publish']['max'] === '' ? '' : intval( $settings['future_publish']['max'] );
      $publish_start  = !isset( $settings['future_publish']['start'] ) || $settings['future_publish']['start'] === '' ? '' : intval( $settings['future_publish']['start'] );
      $publish_end    = !isset( $settings['future_publish']['end'] ) || $settings['future_publish']['end'] === '' ? '' : intval( $settings['future_publish']['end'] );
      ?>
      <!-- Delay publishing by using a range of
      <input name="<?php //echo LTS_PREFIX; ?>settings[future_publish][min]" type="text" id="lts_future_publish_min" value="<?php //echo esc_attr( $timeframe_min ); ?>" class="small-text" />
      to
      <input name="<?php //echo LTS_PREFIX; ?>settings[future_publish][max]" type="text" id="lts_future_publish_max" value="<?php //echo esc_attr( $timeframe_max ); ?>" class="small-text" />
      minutes.
      <br /> -->
      I like to publish only between the hours of
      <input style="width: 10%;" name="<?php echo LTS_PREFIX; ?>settings[future_publish][start]" type="text" id="lts_future_publish_start" value="<?php echo esc_attr( $publish_start); ?>" class="small-text" />
      and
      <input style="width: 10%;" name="<?php echo LTS_PREFIX; ?>settings[future_publish][end]" type="text" id="lts_future_publish_end" value="<?php echo esc_attr( $publish_end ); ?>" class="small-text" />
      <br />
      <span class="description">Leave empty to disable. 24 hour clock.</span>
      <?php
    }

    function use_external_permalink() {
      $settings           = get_option( LTS_PREFIX . 'settings' );
      $external_url   = isset( $settings['external_url'] ) ? true : false;
      ?>
        <input name="<?php echo LTS_PREFIX; ?>settings[external_url]" type="checkbox" id="lts_external_url" value="1" <?php if( $external_url ) : ?>checked="checked"<?php endif; ?>/>
        <span class="description">Use External URL Post</span>
      <?php
    }

    function use_external_permalink_icon(){
      $settings           = get_option( LTS_PREFIX . 'settings' );
      $external_url_icon   = isset( $settings['external_url_icon'] ) ? true : false;
      ?>
        <input name="<?php echo LTS_PREFIX; ?>settings[external_url_icon]" type="checkbox" id="lts_external_url_icon" value="1" <?php if( $external_url_icon ) : ?>checked="checked"<?php endif; ?>/>
        <span class="description">Shows an arrow at the end of the article's title</span>
      <?php
    }

    function edit_prepopulate_slug() {
      $settings           = get_option( LTS_PREFIX . 'settings' );
      $prepopulate_slug   = isset( $settings['prepopulate_slug'] ) ? true : false;
      ?>
        <input name="<?php echo LTS_PREFIX; ?>settings[prepopulate_slug]" type="checkbox" id="lts_prepopulate_slug" value="1" <?php if( $prepopulate_slug ) : ?>checked="checked"<?php endif; ?>/>
        <span class="description">Auto-generate a slug</span>
      <?php
    }

    function edit_support_tags() {
      $settings       = get_option( LTS_PREFIX . 'settings' );
      $support_tags   = isset( $settings['support_tags'] ) ? true : false;
      ?>
        <input name="<?php echo LTS_PREFIX; ?>settings[support_tags]" type="checkbox" id="lts_support_tags" value="1" <?php if( $support_tags ) : ?>checked="checked"<?php endif; ?>/>
        <span class="description">Include a field for tags</span>
      <?php
    }

    function edit_bookmarklet() {
      $lts_bookmarklet = "javascript:var%20d=document,w=window,e=w.getSelection,k=d.getSelection,x=d.selection,s=(e?e():(k)?k():(x?x.createRange().text:0)),f='" . LTS_URL . "',l=d.location,e=encodeURIComponent,u=f+'?u='+e(l.href.replace(new RegExp('(https?:\/\/)','gm'),''))+'&t='+e(d.title)+'&s='+e(s)+'&v=4&m='+(((l.href).indexOf('https://',0)===0)?1:0);a=function(){if(!w.open(u,'t','toolbar=0,resizable=1,scrollbars=1,status=1,width=720,height=570'))l.href=u;};if%20(/Firefox/.test(navigator.userAgent))%20setTimeout(a,%200);%20else%20a();void(0)";
      ?>
      <p class="description"><?php _e( 'Drag the bookmarklet below to your bookmarks bar. Then, when you&#8217;re on a page you want to share, simply &#8220;press&#8221; it.' ); ?></p>
      <p class="pressthis-bookmarklet-wrapper">
        <style>
          .book-icon:before{
            content:"" !important;
          }
        </style>
        <a class="pressthis-bookmarklet" onclick="return false;" href="<?php echo $lts_bookmarklet; ?>">
          <span class="book-icon" style="padding: 8px 12px 8px 9px;"><?php _e( 'Link To Site →' ); ?></span>
        </a>
      </p>
      <?php
    }
    function assets() {
      // add options menu
      add_options_page( 'Settings', 'Link To Site', 'manage_options', __FILE__, array( $this, 'options' ) );
    }

    function options() {
      include 'lts-options.php';
    }
  }
  new linkToSite();
?>
