<?php

// =============================================================================
// FUNCTIONS.PHP
// -----------------------------------------------------------------------------
// Overwrite or add your own custom functions to X in this file.
// =============================================================================

// =============================================================================
// TABLE OF CONTENTS
// -----------------------------------------------------------------------------
//   01. Enqueue Parent Stylesheet
//   02. Additional Functions
// =============================================================================

// Enqueue Parent Stylesheet
// =============================================================================

add_filter( 'x_enqueue_parent_stylesheet', '__return_true' );



// Additional Functions
// =============================================================================

/**
 * Add addtion style and script files
 */

add_action( 'wp_enqueue_scripts', 'enqueue_my_styles', 1000);
function enqueue_my_styles() {
  wp_enqueue_style( 'addition-styles', get_theme_root_uri() . '/x-child/css/addition-styles.css' );
  wp_enqueue_script( 'newletter-signup-scripts', get_theme_root_uri() . '/x-child/js/landing.js' );
  wp_enqueue_script( 'addition-scripts', get_theme_root_uri() . '/x-child/js/addition-scripts.js' );
}

/**
 * Action add user to Mailchimp list
 */

add_action('wp_ajax_mailchimp_subscribe', 'mailchimp_subscribe');
add_action('wp_ajax_nopriv_mailchimp_subscribe', 'mailchimp_subscribe');

function mailchimp_subscribe() {
  $api_key = "e725fa8d2ae7d39163cd76b696a68854-us13";
  $list_id = "230cd8d045";
  
  require('Mailchimp.php');
  
  $Mailchimp = new Mailchimp( $api_key );
  $Mailchimp_Lists = new Mailchimp_Lists( $Mailchimp );
  
  $subscriber = $Mailchimp_Lists->subscribe( $list_id, array('email' => htmlentities($_POST['email'])), array('FNAME' => $_POST['name']), 'html', false, true );
  if ( ! empty( $subscriber['leid'] ) ) {
    echo "success";
  }
  else {
    echo "fail";
  }
  exit();
}

/**
 * Quickview
 */

function add_ajaxurl() {
  echo '<script type="text/javascript">
          var ajaxurl = "'.admin_url('admin-ajax.php').'";
        </script>';
  }
add_action( 'wp_footer', 'add_ajaxurl', 100 );

function quickview_action() {
?>
  <div id="quickViewOverlay"></div>
  <div id="quickViewResponseData"></div>
  <script type="text/javascript">
    jQuery(document).ready(function(){
      jQuery('a[href*="getproductquickview"]').on('click', function(e){ // filter quick view links
        e.preventDefault();
        jQuery('#quickViewResponseData').html('<div class="loading-div"><img src="<?php echo get_theme_root_uri();?>/x-child/images/ajax-loader.gif"/></div>');
        jQuery('#quickViewResponseData').css('display', 'flex');
        jQuery('#quickViewOverlay').css('display', 'block');

        var data = {
          'action': 'my_action',
          'productUrl': jQuery(this).attr("href")
        };

        jQuery.post(ajaxurl, data, function(response) {
          jQuery('#quickViewResponseData').html(response);
        });
      });
    });
  </script>
<?php
}

add_action( 'wp_footer', 'quickview_action' );
add_action( 'wp_enqueue_scripts', 'add_frontend_ajax_javascript_file' );

function add_frontend_ajax_javascript_file() {
  wp_localize_script( 'frontend-ajax', 'frontendajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
}

add_action( 'wp_ajax_my_action', 'get_product_responsse' );
add_action( 'wp_ajax_nopriv_my_action', 'get_product_responsse' );

function get_product_responsse() {
  $json = file_get_contents($_POST['productUrl']);
  $obj = json_decode($json);
?>
  <div class="close-quickview">
    <img src="<?php echo get_theme_root_uri();?>/x-child/images/icon-close.png"/>
  </div>
  <script>
    jQuery(document).ready(function(){
      jQuery('.close-quickview').click(function(){
        jQuery('#quickViewResponseData').hide();
        jQuery('#quickViewOverlay').hide();
      });

      //change value of button
      jQuery('.btn').val("ADD TO CART");
    });
  </script>
<?php
  $str =  str_replace("$(", "jQuery(", $obj->content);
  echo $str;
  wp_die();
}