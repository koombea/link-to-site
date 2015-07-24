jQuery(document).ready(function(){
  jQuery( "body" ).on( "click", ".js-toggle-extra-fields", function( event ) {
    event.preventDefault();
    var $container = jQuery(".js-post-extra-fields");
    jQuery(this).toggleClass("show-extra-fields");
    $container.toggle("fast")
  });

  jQuery("#title").on("keyup paste", function() {
    var newSlug = jQuery(this).val().trim().replace(/["~!@#$%^&*\(\)_+=`{}\[\]\|\\:;'<>,.\/?"\- \t\r\n]+/g, '-').toLowerCase();
    jQuery("#slug").val(newSlug);
  });
});

