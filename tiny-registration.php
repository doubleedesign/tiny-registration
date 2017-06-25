<?php 
/*
Plugin Name: Tiny Registration
Plugin URI: http://wp.tribuna.lt/tiny-login
Description: A simple front-end registration form for WordPress, forked from tinyLogin (http://wp.tribuna.lt/tiny-login). Adds template tags and shortcodes. Shortcodes: [tiny_form_register]. Template tags: get_tiny_form_register() and the_tiny_form_register(). Original code URL: https://gist.github.com/ideag/5582347#file-tiny_login-php
Version: 0.1
Author: Leesa Ward / Double-E Design
Author URI: http://www.doubleedesign.com.au
Text Domain: tiny_registration
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
  echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
  exit;
}

/*=================================================
	REGISTRATION FORM 
=================================================*/
// usage: $result = get_tiny_form_register();
function get_tiny_form_register($redirect=false) {
  global $tiny_form_count;
  ++$tiny_form_count;
  if (!is_user_logged_in()) :
    $return = "<form action=\"\" method=\"post\" class=\"tiny_form tiny_form_register\">\r\n";
    $error = get_tiny_error($tiny_form_count);
    if ($error)
      $return .= "<p class=\"error\">{$error}</p>\r\n";
    $success = get_tiny_success($tiny_form_count);
    if ($success)
      $return .= "<p class=\"success\">{$success}</p>\r\n";

  // add as many inputs, selects, textareas as needed
    $return .= "  <p>
      <label for=\"tiny_username\">".__('Username','tiny_login')."</label>
      <input type=\"text\" id=\"tiny_username\" name=\"tiny_username\"/>
    </p>\r\n";
	$return .= "  <p>
      <label for=\"tiny_firstname\">".__('First name','tiny_login')."</label>
      <input type=\"text\" id=\"tiny_firstname\" name=\"tiny_firstname\"/>
    </p>\r\n";
	$return .= "  <p>
      <label for=\"tiny_lastname\">".__('Last name','tiny_login')."</label>
      <input type=\"text\" id=\"tiny_lastname\" name=\"tiny_lastname\"/>
    </p>\r\n";
    $return .= "  <p>
      <label for=\"tiny_email\">".__('Email','tiny_login')."</label>
      <input type=\"email\" id=\"tiny_email\" name=\"tiny_email\"/>
    </p>\r\n";
  // where to redirect on success
    if ($redirect)
      $return .= "  <input type=\"hidden\" name=\"redirect\" value=\"{$redirect}\">\r\n";
   
    $return .= "  <input type=\"hidden\" name=\"tiny_action\" value=\"register\">\r\n";
    $return .= "  <input type=\"hidden\" name=\"tiny_form\" value=\"{$tiny_form_count}\">\r\n";
   
    $return .= "  <button type=\"submit\">".__('Register','tiny_login')."</button>\r\n";
    $return .= "</form>\r\n";
  else : 
    $return = __('User is logged in.','tiny_login');
  endif;
  return $return;
}
// print form #1
/* usage: <?php the_tiny_form_register(); ?> */
function the_tiny_form_register($redirect=false) {
  echo get_tiny_form_register($redirect);
}
// shortcode for form #1
// usage: [tiny_form_register] in post/page content
add_shortcode('tiny_form_register','tiny_form_register_shortcode');
function tiny_form_register_shortcode ($atts,$content=false) {
  $atts = shortcode_atts(array(
    'redirect' => false
  ), $atts);
  return get_tiny_form_register($atts['redirect']);
}

 
/*=================================================
	FORM SUBMISSION HANDLER
=================================================*/
add_action('init','tiny_handle');
function tiny_handle() {
  $success = false;
  if (isset($_REQUEST['tiny_action'])) {
    switch ($_REQUEST['tiny_action']) {
      case 'register':
        if (!$_POST['tiny_username']) {
          set_tiny_error(__('<strong>ERROR</strong>: Empty username','tiny_login'),$_REQUEST['tiny_form']);
        } else if (!$_POST['tiny_email']) {
          set_tiny_error(__('<strong>ERROR</strong>: Empty email','tiny_login'),$_REQUEST['tiny_form']);
        } else {
          $creds = array();
          $creds['first_name'] = $_POST['tiny_firstname'];
          $creds['last_name'] = $_POST['tiny_lastname'];
		  $creds['user_login'] = $_POST['tiny_username'];
          $creds['user_email'] = $_POST['tiny_email'];
          $creds['user_pass'] = wp_generate_password();
          $creds['role'] = get_option('default_role');
          //$creds['remember'] = false;
          $user = wp_insert_user( $creds );
          if ( is_wp_error($user) ) {
            set_tiny_error($user->get_error_message(),$_REQUEST['tiny_form']);
          } else {
            set_tiny_success(__('Registration successful. Your password will be sent via email shortly. Once approved by a site admin, you will be able to access members-only content.','tiny_login'),$_REQUEST['tiny_form']);
            wp_new_user_notification($user,null,'both');
            $success = true;
          }
        }
        break;
      // add more cases if you have more forms
    }
 
    // if redirect is set and action was successful
    if (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] && $success) {
      wp_redirect($_REQUEST['redirect']);
      die();
    }      
  }
}


// ================= UTILITIES

if (!function_exists('set_tiny_error')) {
  function set_tiny_error($error,$id=0) {
    $_SESSION['tiny_error_'.$id] = $error;
  }
}
// shows error message
if (!function_exists('the_tiny_error')) {
  function the_tiny_error($id=0) {
    echo get_tiny_error($id);
  }
}
 
if (!function_exists('get_tiny_error')) {
  function get_tiny_error($id=0) {
    if ($_SESSION['tiny_error_'.$id]) {
      $return = $_SESSION['tiny_error_'.$id];
      unset($_SESSION['tiny_error_'.$id]);
      return $return;
    } else {
      return false;
    }
  }
}
if (!function_exists('set_tiny_success')) {
  function set_tiny_success($error,$id=0) {
    $_SESSION['tiny_success_'.$id] = $error;
  }
}
if (!function_exists('the_tiny_success')) {
  function the_tiny_success($id=0) {
    echo get_tiny_success($id);
  }
}
 
if (!function_exists('get_tiny_success')) {
  function get_tiny_success($id=0) {
    if ($_SESSION['tiny_success_'.$id]) {
      $return = $_SESSION['tiny_success_'.$id];
      unset($_SESSION['tiny_success_'.$id]);
      return $return;
    } else {
      return false;
    }
  }
}

?>