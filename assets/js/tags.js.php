<?php if ( !empty( $settings['support_tags'] ) ) :
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
