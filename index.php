<?php
global $lts_debug;
global $default_link;
$lts_debug = false;

define('IFRAME_REQUEST' , true);
ob_start();

$location = realpath($_SERVER["DOCUMENT_ROOT"]);
include ($location . '/wp-load.php');
include ($location . '/wp-admin/includes/admin.php');
include ($location . '/wp-admin/admin.php');

ob_end_clean();

header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );

if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) {
  wp_die( __( 'Access Denied.' ) );
}

  // let's create our post
$post       = get_default_post_to_edit( 'post', true );
$post_ID    = absint( $post->ID );

if( $lts_debug ) {
  error_log( '$post_ID = ' . $post_ID );
}

  // Set Variables
$title = isset( $_GET['t'] ) ? trim( strip_tags( html_entity_decode( stripslashes( $_GET['t'] ) , ENT_QUOTES) ) ) : '';

if( $lts_debug ) {
  error_log( '$title = ' . $title );
}

$selection = '';

if ( ! empty( $_GET['s'] ) ) {
  $selection = str_replace( '&apos;', "'", stripslashes( $_GET['s'] ) );
  $selection = trim( htmlspecialchars( html_entity_decode( $selection, ENT_QUOTES ) ) );
}


  $selection = preg_replace('/(\r?\n|\r)/', '</p><p>', $selection);
  $selection = '<blockquote>' . str_replace('<p></p>', '', $selection) . '</blockquote>';
  $format = get_post_format();
  if ( false === $format ) {
    $format = 'standard'; // WP Default Name
  }
  $selection = $selection;


if ( $lts_debug ) {
 error_log( '$selection = ' . $selection );
}

  // we stripped the protocol so as to avoid issues with certain
  // webhosts (HostGator) that throw 404's if protocols are in GET vars
  // but we tracked if it was HTTPS so we'll put the protocol back in
$url = isset( $_GET['u'] ) ? esc_url( ( $_GET['m'] ? 'https://' : 'http://' ) . $_GET['u'] ) : '';

if( $lts_debug )
  error_log( '$url = ' . $url );

$image = isset( $_GET['i'] ) ? $_GET['i'] : '';

if( $lts_debug ) {
  error_log( '$image = ' . $image );
}

function lts_post(){
  global $lts_debug;
  $settings = get_option( LTS_PREFIX . 'settings' );
  // set our time (if applicable)
  $timeframe_min  = !isset( $settings['future_publish']['min'] ) || $settings['future_publish']['min'] === '' ? false : intval( $settings['future_publish']['min'] );
  $timeframe_max  = !isset( $settings['future_publish']['max'] ) || $settings['future_publish']['max'] === '' ? false : intval( $settings['future_publish']['max'] );
  $publish_start  = !isset( $settings['future_publish']['start'] ) || $settings['future_publish']['start'] === '' ? false : intval( $settings['future_publish']['start'] );
  $publish_end    = !isset( $settings['future_publish']['end'] ) || $settings['future_publish']['end'] === '' ? false : intval( $settings['future_publish']['end'] );
  // by default it'll be right now
  $timestamp      = (int) current_time( 'timestamp' );
  $timestamp_gmt  = (int) current_time( 'timestamp', 1 );
  if( $lts_debug ) {
    error_log( '$timestamp (source) = ' . $timestamp . ' ' . date( 'Y-m-d H:i:s', $timestamp ) );
    error_log( '$timestamp_gmt (source) = ' . $timestamp_gmt . ' ' . date( 'Y-m-d H:i:s', $timestamp_gmt ) );
  }
  $future_publish = false;
  // check to see if we need to bump our publish time
  if ( $timeframe_min !== false && $timeframe_max !== false ) {
    // set the post date
    if( $lts_debug ) {
      error_log( 'trigger: timeframe' );
    }

    // figure out our start time which is either right now, or the future-most post
    $args = array(
      'numberposts'   => 1,
      'post_status'   => array( 'publish', 'pending', 'future' )
      );
    $posts_array = get_posts( $args );

    // if there are any posts, we can check it out
    $post_timestamp = false;
    if ( $posts_array ) {

      if ( $lts_debug ) {
        error_log( 'found post' );
      }
      foreach ( $posts_array as $post ) {
        setup_postdata( $post );
        $post_timestamp = strtotime( $post->post_date );// local time
        $post_timestamp_gmt = strtotime( $post->post_date_gmt );

        if ( $lts_debug ) {
          error_log( print_r( $post, true ) );
          error_log( '======================' );
        }
      }

      if ( $lts_debug ) {
        error_log( '$post_timestamp = ' . $post_timestamp . ' ' . date( 'Y-m-d H:i:s', $post_timestamp ) );
        error_log( '$timestamp = ' . $timestamp . ' ' . date( 'Y-m-d H:i:s', $timestamp ) );
      }
    }

    // get the future-most timestamp and use that
    if ( ( $post_timestamp + ( $timeframe_min * 60 ) ) > $timestamp ) { // $timestamp is still now() in local time
      $future_publish = true;
      if ( $lts_debug ) {
        error_log( 'FUTURE PUBLISH' );
      }
      // our timestamps need to be adjusted
      $timestamp      = $post_timestamp;
      $timestamp_gmt  = $post_timestamp_gmt;

      if ( $lts_debug ) {
        error_log( '$timestamp (before 1) = ' . $timestamp . ' ' . date( 'Y-m-d H:i:s', $timestamp ) );
        error_log( '$timestamp_gmt (before 1) = ' . $timestamp_gmt . ' ' . date( 'Y-m-d H:i:s', $timestamp_gmt ) );
      }

      // determine how many seconds we'll offset
      $offset = rand( $timeframe_min * 60, $timeframe_max * 60 );
      if ( $lts_debug ) {
        error_log( '$offset (in seconds) = ' . $offset );
      }

      // the post is scheduled so we need to offset both
      $timestamp      = $timestamp + $offset;
      $timestamp_gmt  = $timestamp_gmt + $offset;
      if ( $lts_debug ) {
        error_log( '$timestamp (after) = ' . $timestamp . ' ' . date( 'Y-m-d H:i:s', $timestamp ) );
        error_log( '$timestamp_gmt (after) = ' . $timestamp_gmt . ' ' . date( 'Y-m-d H:i:s', $timestamp_gmt ) );
        error_log( 'NEW FUTURE PUBLISH TIME: ' . date( 'Y-m-d H:i:s', $timestamp ) );
      }
    }
  }
  // we need to check to see if we're within the posting window (if set)
  if ( $publish_start !== false && $publish_end !== false ) {
    if ( $repost_debug ) {
      error_log( 'checking publish window...' );
    }

    // our publish window needs to be put within today's context
    $publish_start  = date( 'U', strtotime( date( 'Y-m-d' ) . ' ' . $publish_start . ':00:00' ) );
    $publish_end    = date( 'U', strtotime( date( 'Y-m-d' ) . ' ' . $publish_end . ':00:00' ) );

    if ( $repost_debug ) {
      error_log( 'window: ' . $publish_start . ' - ' . $publish_end );
    }

    // check to see if we're too early
    if ( $timestamp < $publish_start ) {

      if ( $repost_debug ) {
        error_log( 'too early' );
      }

      $future_publish     = true;
      $timestamp          = $publish_start;
      $timestamp_gmt      = $publish_start - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

      if ( $repost_debug ) {
        error_log( '$timestamp (after) = ' . $timestamp . ' ' . date( 'Y-m-d H:i:s', $timestamp ) );
        error_log( '$timestamp_gmt (after) = ' . $timestamp_gmt . ' ' . date( 'Y-m-d H:i:s', $timestamp_gmt ) );
      }
    }

    // check to see if we're too late
    if ( $timestamp > $publish_end ) {
      if ( $repost_debug ) {
        error_log( 'too late' );
      }

        // need to push it to tomorrow's start time
      $future_publish     = true;
      $timestamp          = $publish_start + ( 24 * 60 * 60 );
      $timestamp_gmt      = ( $publish_start + ( 24 * 60 * 60 ) ) - ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );

      if ( $repost_debug ) {
        error_log( '$timestamp (after) = ' . $timestamp . ' ' . date( 'Y-m-d H:i:s', $timestamp ) );
        error_log( '$timestamp_gmt (after) = ' . $timestamp_gmt . ' ' . date( 'Y-m-d H:i:s', $timestamp_gmt ) );
      }
    }
  }

  $settings   = get_option( LTS_PREFIX . 'settings' );
  $post       = get_default_post_to_edit();
  $post       = get_object_vars( $post );
  $post_ID    = $post['ID'] = intval( $_POST['post_id'] );

  if ( ! current_user_can( 'edit_post', $post_ID ) ) {
    wp_die( __( 'You are not allowed to edit this post.' ) );
  }

  // set our category
  $post['post_category']  = ! empty( $settings['category'] ) ? intval( $settings['category'] ) : 0;

  // set our post properties
  $post['post_title']     = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
  $post['post_title']     = $post['post_title'];
  $content                = isset( $_POST['content'] ) ? $_POST['content'] : '';

  // set the post_content and status
  $post['post_content']   = wp_kses_post( $content );
  $post['post_status']    = 'draft';

  // set our post format
  if ( isset( $settings['post_format'] ) ) {
    if ( current_theme_supports( 'post-formats', $settings['post_format'] ) ) {
      set_post_format( $post_ID, $settings['post_format'] );
    } else {
      set_post_format( $post_ID, false );
    }
  }

  // set the category
  $post['post_category'] = array_map( 'absint', array( $post['post_category'] ) );
  // set the slug
  $post['post_name'] = sanitize_title( $_POST['slug'] );
  // update what we've set
  $post_ID = wp_update_post( $post );
  update_post_meta($post_ID,'lts_1810', esc_url( $_POST['url'] ));
  // set our post tags if applicable
  if ( ! empty( $settings['support_tags'] ) && ! empty( $_POST['tags'] ) ) {
    wp_set_post_tags( $post_ID, $_POST['tags'] );
  }

  // mark as published if that's the intention
  if ( isset( $_POST['publish'] ) && current_user_can( 'publish_posts' ) ) {
    if ( $future_publish ) {
      $post['post_status'] = 'future';
      if ( $lts_debug ) {
        error_log( '*** altering timestamps' );
      }
      $post['edit_date']      = date( 'Y-m-d H:i:s', $timestamp );
      $post['post_date']      = date( 'Y-m-d H:i:s', $timestamp );
      $post['post_date_gmt']  = date( 'Y-m-d H:i:s', $timestamp_gmt );
      if ( $lts_debug ) {
        error_log( print_r( $post, true ) );
        error_log( '======================' );
      }
    } else {
      $post['post_status'] = 'publish';
    }
  }
  // our final update
  $post_ID = wp_update_post( $post );
  return $post_ID;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php wp_title(); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <?php do_action( 'admin_print_scripts' ); do_action('admin_head'); ?>
    <link rel="stylesheet" href="<?php echo plugins_url( 'assets/css/jqueryui.css', __FILE__ ); ?>" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?php echo plugins_url( 'assets/css/style.css', __FILE__ ); ?>" type="text/css" media="screen" />
    <link rel="stylesheet" href="<?php echo plugins_url( 'assets/css/overwrite.css', __FILE__ ); ?>" type="text/css" media="screen" />
  </head>
  <body>
    <?php
    if ( isset( $_REQUEST['_wpnonce'] ) ) {
      check_admin_referer( 'lts-press-this' );
      $posted = $post_ID = lts_post();
      ?>
      <header class="header">
        <h1 class="page-title">
          <img src="assets/images/quotes.svg" alt="">
          <?php wp_title(); ?>
        </h1>
      </header>
      <div class="message">
        <p>Your post have been published!</p>
        <a class="view-post-button" onclick="window.opener.location.replace(this.href); window.close();" href="<?php echo wp_get_shortlink($post_ID); ?>">View post</a>
      </div>
      <?php } else { ?>
      <?php $settings = get_option( LTS_PREFIX . 'settings' ); ?>
      <header class="header">
        <h1 class="page-title">
          <img src="assets/images/quotes.svg" alt="">
          <?php wp_title(); ?>
        </h1>
        <a class="close-post-button" onclick="window.close();" href="#"><?php _e( 'Close' ) ?></a>
      </header>
      <form class="form" action="" method="post">
        <div class="hidden">
          <?php wp_nonce_field( 'lts-press-this' ); ?>
          <input type="hidden" name="post_type" id="post_type" value="text"/>
          <input type="hidden" name="autosave" id="autosave" />
          <input type="hidden" id="original_post_status" name="original_post_status" value="draft" />
          <input type="hidden" id="prev_status" name="prev_status" value="draft" />
          <input type="hidden" id="post_id" name="post_id" value="<?php echo absint( $post_ID ); ?>" />
        </div>
        <div class="field textfield" id="row-title">
          <label class="label" for="title">TITLE</label>
          <input class="input-text" type="text" name="title" id="title" value="<?php echo esc_attr( $title ); ?>" />
          <a href="javascript:;" class="extra-fields-link js-toggle-extra-fields"></a>
        </div>
        <div class="js-post-extra-fields hidden">
          <div class="field textfield" id="row-url">
            <label class="label" for="url">TITLE LINK</label>
            <input class="input-text" type="text" name="url" id="url" value="<?php echo esc_url( $url ); ?>" readonly  />
          </div>
          <div class="field textfield" id="row-slug">
            <label class="label" for="slug">PERMALINK SLUG</label>
            <input class="input-text" type="text" name="slug" id="slug" value="<?php if( isset( $settings['prepopulate_slug'] ) ) { echo sanitize_title( $title ); } ?>" />
          </div>
          <?php if( ! empty( $settings['support_tags'] ) ){?>
            <div class="field textfield" id="row-tags">
              <label class="label" for="url">TAGS</label>
              <input class="input-text" type="text" name="tags" id="tags" value="" placeholder="<?php _e( 'Separate tags with commas' ) ?>"/>
            </div>
          <?php } ?>
        </div>
        <div class="field textarea" id="row-content">
          <label for="content" class="hidden">Content</label>
          <?php
            wp_editor( $selection, 'content', array(
              'drag_drop_upload' => true,
              'editor_height'    => 230,
              'media_buttons'    => false,
              'textarea_name'    => 'content',
              'teeny'            => true,
              'tinymce'          => array(
                'resize'                => false,
                'content_css' => plugins_url('/assets/css/editor.css' , __FILE__ ),
                'wordpress_adv_hidden'  => false,
                'add_unload_trigger'    => false,
                'statusbar'             => false,
                'autoresize_min_height' => 350,
                'autoresize_max_height' => 350,
                'wp_autoresize_on'      => true,
                'plugins'               => 'lists,media,paste,tabfocus,fullscreen,wordpress,wpautoresize,wpeditimage,wpgallery,wplink,wpview',
                'toolbar1'              => 'bold,italic,underline,blockquote,link,unlink'
                ),
              'quicktags' => false
              ) );
            ?>
          </div>
          <div class="actions" id="row-actions">
            <div class="action__item">
              <input class="btn-save" type="submit" name="save" id="save" value="Save" />
            </div>
            <div class="action__item">
              <input class="btn-publish" type="submit" name="publish" id="publish" value="Publish" />
            </div>
          </div>
      </form>
        <?php }
      wp_enqueue_script('main', plugins_url( '/assets/js/main.js' , __FILE__ ), array( 'jquery' ), true);
      wp_enqueue_script( 'underscore' );
      wp_enqueue_script( 'jquery-ui-autocomplete' );
      wp_enqueue_style( 'jquery-ui' );
      do_action('admin_footer');
      do_action('admin_print_footer_scripts');

      if ( !empty( $settings['support_tags'] ) ) :
        $args = array( 'hide_empty' => false );
        $tags = get_tags( $args );
        foreach( $tags as $tag ) {
          $all_tags[] = '"' . str_replace( '"', '\"', esc_js( $tag->name ) ) . '"';
        }
      ?>
      <script type="text/javascript">
        var LTS_TAGS = [<?php echo implode( ',', $all_tags ); ?>];
        function split( val ) {
          return val.split( /,\s*/ );
        }
        function extractLast( term ) {
          return split( term ).pop();
        }

        jQuery('#tags')
          // don't navigate away from the field on tab when selecting an item
          .bind( "keydown", function( event ) {
            if ( event.keyCode === jQuery.ui.keyCode.TAB &&
              jQuery( this ).data( "autocomplete" ).menu.active ) {
              event.preventDefault();
            }
          })
          .autocomplete({
            minLength: 0,
            source: function( request, response ) {
              // delegate back to autocomplete, but extract the last term
              response( jQuery.ui.autocomplete.filter(
                LTS_TAGS, extractLast( request.term ) ) );
            },
            focus: function() {
              // prevent value inserted on focus
              return false;
            },
            select: function( event, ui ) {
              var terms = split( this.value );
              // remove the current input
              terms.pop();
              // add the selected item
              terms.push( ui.item.value );
              // add placeholder to get the comma-and-space at the end
              terms.push( "" );
              this.value = terms.join( ", " );
              return false;
            }
          });
      </script>
    <?php endif; ?>
  </body>
</html>
