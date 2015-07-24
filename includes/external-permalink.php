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

  global $default_link;
  global $k_meta;
  function add_meta_boxes() {
    add_meta_box(
      'lts-1810',
      'Link To Site - URL External Permalink',
      'lts_meta_box_display',
      'post',
      'normal',
      'high'
    );
  }add_action('admin_menu', 'add_meta_boxes');

  function lts_meta_box_display() {
    global $post;
    wp_nonce_field( 'lts_1810', 'lts_1810_nonce' );
    $meta = get_post_meta($post->ID, 'lts_1810', true);
    $lts_url = isset($meta) ? $meta : '';?>
    <input type="text" class="widefat" name="lts_1810" value="<?php echo $lts_url; ?>" placeholder="http://"/><?php
  }

  function lts_1810_meta_box_save($post_id) {
    global $custom_meta_fields;
    if ( ! isset( $_POST['lts_1810_nonce'] ) ||
      ! wp_verify_nonce( $_POST['lts_1810_nonce'], 'lts_1810' ) )
      return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
      return;
    if (!current_user_can('edit_post', $post_id))
      return;
    update_post_meta($post_id,'lts_1810',$_POST['lts_1810']);
  }add_action('save_post', 'lts_1810_meta_box_save');

  function lts_1810_row_actions( $actions) {
    global $default_link;
    $actions['view'] = "<a href='" . $default_link . "'>" . __( 'View' ) ."</a>";
    return $actions;
  }add_filter( 'post_row_actions', 'lts_1810_row_actions', 10, 2 );


  function lts_1810_post_content($content) {
    global $default_link;
    global $k_meta;
    if ( in_the_loop() && !is_page() && !is_single() ) {
      if($k_meta){
        $content = $content . '<a style="text-decoration: none;font-size: 12px" href="'. $default_link .'"><i style="vertical-align: -2px;margin-right: 5px;font-size: 24px;">∞</i>Permalink</a>';
      }
    }
    return $content;
  }add_filter( 'the_content', 'lts_1810_post_content', 10, 2  );


  function lts_1810_external_permalink( $link, $post ) {
    global $default_link;
    global $k_meta;
    $default_link = $link;
    $meta = get_post_meta( $post->ID, 'lts_1810', true );
    $url  = esc_url( filter_var($meta, FILTER_VALIDATE_URL ) );
    $k_meta = $url;
    return $url ? $url : $link;
  }add_filter( 'post_link', 'lts_1810_external_permalink', 10, 2 );
  $settings       = get_option( LTS_PREFIX . 'settings' );
  $external_url_icon = isset( $settings['external_url_icon'] ) ? sanitize_key( $settings['external_url_icon'] ) : false;
  if ( ! empty( $external_url_icon ) ) {
    function lts_1810_modified_post_title ($title) {
      global $post;
      if (!empty( $post )){
        $meta = get_post_meta( $post->ID, 'lts_1810', true );
        if($meta){
          if (in_the_loop() && !is_page() && !is_single()) {
            $title = $title . ' →';
          }elseif(in_the_loop() && is_single() && !is_page()){
            $title = "<a rel='bookmark' target='_blank' href='" . $meta . "'>" . $title . ' →' . "</a>";
          }
        }
      }
      return $title;
    }add_filter( 'the_title', 'lts_1810_modified_post_title');
  }
  /**
   * Filter previous_post_link and next_post_link
   */
  function filter_next_post_link($link) {
    return "";
  }add_filter('next_post_link', 'filter_next_post_link');

  function filter_previous_post_link($link) {
    return "";
  }add_filter('previous_post_link', 'filter_previous_post_link');

  function top_form_edit( $post) {
    global $default_link;
    $default_link = str_replace("%postname%",$post->post_name,$default_link);
    if($post )
      echo "<hr><div style='margin: 10px;'><a class='button button-small ' target='_blank' href='" . $default_link . "'>".__( 'View Internal Post' )."</a></div><hr>";
  }add_action( 'edit_form_after_title', 'top_form_edit' );

?>
