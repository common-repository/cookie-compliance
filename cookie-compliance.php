<?php
/*
Plugin Name: EU Cookie Law Compliance
Plugin URI: https://zafrira.net/en/tools/wordpress-plugins/cookie-compliance/
Description: Plugin to help you to make your wordpress installation to comply to the new cookie regulations in the EU.
Version: 1.1.4
Author: zafrira
Author URI: https://zafrira.net
License: GPL2
*/

require dirname(__FILE__) . '/classes/cookie-compliance.php';

register_activation_hook(__FILE__, array('Cookie_Compliance', 'activate'));
add_action('init', array('Cookie_Compliance', 'initialize'));

function cookie_compliance_links($links) { 
  $new_link = '<a href="admin.php?page=cookie-compliance">' . __('Settings') . '</a>'; 
  array_unshift($links, $new_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'cookie_compliance_links' );